<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Events\AgendaUpdated;
use App\Events\AppointmentStatusUpdated;
use App\Exceptions\SlotUnavailableException;
use App\Models\Addon;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The only place where appointments are created or moved.
 *
 * Race condition this prevents: two clients load the page, both see 10:00
 * free and both submit. Checking availability and inserting must happen as
 * one indivisible step, so every write runs inside an atomic lock shared by
 * all requests ("agenda:write"). The second request waits for the first to
 * finish, re-checks availability, sees the overlap and gets a
 * SlotUnavailableException instead of a double booking.
 */
class AppointmentScheduler
{
    public function __construct(private AvailabilityService $availability) {}

    /**
     * @param  Collection<int, Addon>  $addons
     * @param  array{status?: AppointmentStatus, source?: string, client_notes?: ?string, admin_notes?: ?string}  $attributes
     *
     * @throws SlotUnavailableException
     */
    public function book(
        Client $client,
        Service $service,
        Collection $addons,
        CarbonInterface $startsAt,
        array $attributes = [],
        bool $enforceBusinessHours = true,
    ): Appointment {
        $startsAt = CarbonImmutable::instance($startsAt)->startOfMinute();
        $endsAt = $startsAt->addMinutes($service->duration_minutes + $addons->sum('duration_minutes'));

        $appointment = $this->withAgendaLock(function () use ($client, $service, $addons, $startsAt, $endsAt, $attributes, $enforceBusinessHours): Appointment {
            if (! $this->availability->isAvailable($startsAt, $endsAt, enforceBusinessHours: $enforceBusinessHours)) {
                throw new SlotUnavailableException;
            }

            $appointment = Appointment::create([
                'client_id' => $client->id,
                'service_id' => $service->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => $attributes['status'] ?? AppointmentStatus::Pending,
                'total_price' => $service->price + $addons->sum('price'),
                'source' => $attributes['source'] ?? 'online',
                'client_notes' => $attributes['client_notes'] ?? null,
                'admin_notes' => $attributes['admin_notes'] ?? null,
            ]);

            $appointment->addons()->attach($addons->mapWithKeys(fn (Addon $addon): array => [
                $addon->id => ['price' => $addon->price, 'duration_minutes' => $addon->duration_minutes],
            ]));

            return $appointment;
        });

        $this->announce($startsAt);

        return $appointment;
    }

    /**
     * Full edit from the admin form: service, add-ons, time, status and notes.
     *
     * @param  Collection<int, Addon>  $addons
     * @param  array{status: AppointmentStatus, client_id: int, client_notes?: ?string, admin_notes?: ?string}  $attributes
     *
     * @throws SlotUnavailableException
     */
    public function update(
        Appointment $appointment,
        Service $service,
        Collection $addons,
        CarbonInterface $startsAt,
        array $attributes,
        bool $enforceBusinessHours = true,
    ): Appointment {
        $previousStart = $appointment->starts_at;
        $startsAt = CarbonImmutable::instance($startsAt)->startOfMinute();
        $endsAt = $startsAt->addMinutes($service->duration_minutes + $addons->sum('duration_minutes'));

        $this->withAgendaLock(function () use ($appointment, $service, $addons, $startsAt, $endsAt, $attributes, $enforceBusinessHours): void {
            $willOccupy = in_array($attributes['status'], AppointmentStatus::occupying(), true);

            if ($willOccupy && ! $this->availability->isAvailable($startsAt, $endsAt, $appointment->id, $enforceBusinessHours)) {
                throw new SlotUnavailableException;
            }

            $appointment->update([
                ...$attributes,
                'service_id' => $service->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'total_price' => $service->price + $addons->sum('price'),
            ]);

            $appointment->addons()->sync($addons->mapWithKeys(fn (Addon $addon): array => [
                $addon->id => ['price' => $addon->price, 'duration_minutes' => $addon->duration_minutes],
            ]));
        });

        $this->announce($previousStart, $startsAt);

        return $appointment;
    }

    /**
     * Move an appointment (e.g. drag & drop on the admin calendar).
     *
     * @throws SlotUnavailableException
     */
    public function reschedule(Appointment $appointment, CarbonInterface $startsAt, CarbonInterface $endsAt): Appointment
    {
        $previousStart = $appointment->starts_at;
        $startsAt = CarbonImmutable::instance($startsAt)->startOfMinute();
        $endsAt = CarbonImmutable::instance($endsAt)->startOfMinute();

        $this->withAgendaLock(function () use ($appointment, $startsAt, $endsAt): void {
            $holdsSlot = in_array($appointment->status, AppointmentStatus::occupying(), true);

            if ($holdsSlot && ! $this->availability->isAvailable($startsAt, $endsAt, $appointment->id, enforceBusinessHours: false)) {
                throw new SlotUnavailableException;
            }

            $appointment->update(['starts_at' => $startsAt, 'ends_at' => $endsAt]);
        });

        $this->announce($previousStart, $startsAt);

        return $appointment;
    }

    /**
     * Reactivating a cancelled appointment must re-check its slot, since
     * someone else may have booked it in the meantime.
     *
     * @throws SlotUnavailableException
     */
    public function changeStatus(Appointment $appointment, AppointmentStatus $status): Appointment
    {
        $this->withAgendaLock(function () use ($appointment, $status): void {
            $wasFreed = $appointment->status === AppointmentStatus::Cancelled;
            $willOccupy = in_array($status, AppointmentStatus::occupying(), true);

            if ($wasFreed && $willOccupy && ! $this->availability->isAvailable($appointment->starts_at, $appointment->ends_at, $appointment->id, enforceBusinessHours: false)) {
                throw new SlotUnavailableException;
            }

            $appointment->update(['status' => $status]);
        });

       $this->announce($appointment->starts_at);

rescue(fn () => broadcast(
    new AppointmentStatusUpdated($appointment)
));

return $appointment;
    }

    /**
     * Broadcast that availability changed for the given days. A Reverb outage
     * must never make a booking fail, so errors are only reported.
     */
    public function announce(CarbonInterface ...$days): void
    {
        $dates = collect($days)->map(fn (CarbonInterface $day): string => $day->toDateString())->unique()->values()->all();

        rescue(fn () => broadcast(new AgendaUpdated($dates)));
    }

    /**
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    private function withAgendaLock(Closure $callback): mixed
    {
        return Cache::lock('agenda:write', 10)->block(5, fn () => DB::transaction($callback));
    }
}
