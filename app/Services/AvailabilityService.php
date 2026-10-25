<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BusinessHour;
use App\Models\ScheduleBlock;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Answers "is this time free?" and "which start times are free on this day?".
 *
 * A candidate range [start, end) is free when:
 *  1. it fits entirely inside one business_hours range of that weekday,
 *  2. it does not overlap any appointment that still occupies its slot
 *     (pending, confirmed or completed), and
 *  3. it does not overlap any schedule block (breaks, vacations).
 */
class AvailabilityService
{
    /**
     * Free start times ("H:i") for a service of the given duration on a day.
     *
     * @return Collection<int, string>
     */
    public function slotsFor(CarbonInterface $date, int $durationMinutes): Collection
    {
        $day = CarbonImmutable::instance($date)->startOfDay();
        $workingRanges = $this->workingRanges($day);

        if ($workingRanges->isEmpty() || $durationMinutes <= 0) {
            return collect();
        }

        $busyRanges = $this->busyRanges($day, $day->endOfDay());
        $earliestStart = CarbonImmutable::now()->addMinutes(config('salon.min_notice_minutes'));
        $interval = config('salon.slot_interval_minutes');
        $slots = collect();

        foreach ($workingRanges as [$opensAt, $closesAt]) {
            for ($start = $opensAt; $start->addMinutes($durationMinutes) <= $closesAt; $start = $start->addMinutes($interval)) {
                $end = $start->addMinutes($durationMinutes);

                if ($start >= $earliestStart && ! $this->overlapsAny($busyRanges, $start, $end)) {
                    $slots->push($start->format('H:i'));
                }
            }
        }

        return $slots;
    }

    /**
     * Whether [startsAt, endsAt) can be booked.
     *
     * The admin may book outside business hours ($enforceBusinessHours = false),
     * but overlaps with other appointments and blocks are never allowed.
     */
    public function isAvailable(
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        ?int $ignoreAppointmentId = null,
        bool $enforceBusinessHours = true,
    ): bool {
        if ($endsAt <= $startsAt) {
            return false;
        }

        if ($enforceBusinessHours && ! $this->fitsBusinessHours($startsAt, $endsAt)) {
            return false;
        }

        $overlapsAppointment = Appointment::query()
            ->occupying()
            ->overlapping($startsAt, $endsAt)
            ->when($ignoreAppointmentId, fn ($query) => $query->whereKeyNot($ignoreAppointmentId))
            ->exists();

        if ($overlapsAppointment) {
            return false;
        }

        return ! ScheduleBlock::query()->overlapping($startsAt, $endsAt)->exists();
    }

    /**
     * Weekdays (0-6) that have at least one working range.
     *
     * @return list<int>
     */
    public function openWeekdays(): array
    {
        return BusinessHour::query()->distinct()->orderBy('weekday')->pluck('weekday')->all();
    }

    private function fitsBusinessHours(CarbonInterface $startsAt, CarbonInterface $endsAt): bool
    {
        if (! $startsAt->isSameDay($endsAt)) {
            return false;
        }

        return $this->workingRanges(CarbonImmutable::instance($startsAt)->startOfDay())
            ->contains(fn (array $range): bool => $startsAt >= $range[0] && $endsAt <= $range[1]);
    }

    /**
     * @return Collection<int, array{0: CarbonImmutable, 1: CarbonImmutable}>
     */
    private function workingRanges(CarbonImmutable $day): Collection
    {
        return BusinessHour::query()
            ->where('weekday', $day->dayOfWeek)
            ->orderBy('opens_at')
            ->get()
            ->map(fn (BusinessHour $hours): array => [
                $day->setTimeFromTimeString($hours->opens_at),
                $day->setTimeFromTimeString($hours->closes_at),
            ]);
    }

    /**
     * @return Collection<int, array{0: CarbonImmutable, 1: CarbonImmutable}>
     */
    private function busyRanges(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        $appointments = Appointment::query()
            ->occupying()
            ->overlapping($from, $to)
            ->get(['starts_at', 'ends_at']);

        $blocks = ScheduleBlock::query()
            ->overlapping($from, $to)
            ->get(['starts_at', 'ends_at']);

        return $appointments->toBase()->merge($blocks)->map(fn ($busy): array => [
            CarbonImmutable::instance($busy->starts_at),
            CarbonImmutable::instance($busy->ends_at),
        ]);
    }

    /**
     * @param  Collection<int, array{0: CarbonImmutable, 1: CarbonImmutable}>  $busyRanges
     */
    private function overlapsAny(Collection $busyRanges, CarbonImmutable $start, CarbonImmutable $end): bool
    {
        return $busyRanges->contains(fn (array $busy): bool => $start < $busy[1] && $busy[0] < $end);
    }
}
