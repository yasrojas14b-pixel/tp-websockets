<?php

namespace App\Http\Controllers;

use App\Exceptions\SlotUnavailableException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Addon;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use App\Services\AppointmentScheduler;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(AvailabilityService $availability): View
    {
        $services = Service::query()
            ->active()
            ->with(['addons' => fn ($query) => $query->active()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $openWeekdays = $availability->openWeekdays();

        $days = collect(CarbonImmutable::today()->daysUntil(CarbonImmutable::today()->addDays(config('salon.booking_window_days'))))
            ->map(fn (CarbonImmutable $day): array => [
                'value' => $day->toDateString(),
                'weekday' => $day->isToday() ? 'Hoy' : ucfirst($day->translatedFormat('D')),
                'day' => $day->day,
                'month' => $day->translatedFormat('M'),
                'isOpen' => in_array($day->dayOfWeek, $openWeekdays, true),
            ]);

        return view('booking.create', [
            'services' => $services,
            'days' => $days,
        ]);
    }

    public function store(StoreBookingRequest $request, AppointmentScheduler $scheduler): RedirectResponse
    {
        $service = $request->service();
        $addons = Addon::query()->active()->whereKey($request->input('addons', []))->get();

        $client = Client::firstOrCreate(
            ['phone' => Client::normalizePhone($request->string('phone'))],
            ['name' => $request->string('name')],
        );

        try {
            $appointment = $scheduler->book(
                $client,
                $service,
                $addons,
                CarbonImmutable::createFromFormat('Y-m-d H:i', $request->input('date').' '.$request->input('time')),
                ['client_notes' => $request->input('client_notes'), 'source' => 'online'],
            );
        } catch (SlotUnavailableException $exception) {
            return back()->withInput()->withErrors(['time' => $exception->getMessage()]);
        }

        return redirect()->route('booking.show', $appointment);
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['service', 'addons', 'client']);

        return view('booking.show', ['appointment' => $appointment]);
    }
}
