<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Exceptions\SlotUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppointmentRequest;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use App\Services\AppointmentScheduler;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $appointments = Appointment::query()
            ->with(['client', 'service'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when(
                $filters['date'] ?? null,
                fn ($query, $date) => $query->whereDate('starts_at', $date),
                fn ($query) => $query->where('starts_at', '>=', today()),
            )
            ->orderBy('starts_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'statuses' => AppointmentStatus::cases(),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $startsAt = $request->filled('start')
            ? rescue(fn () => CarbonImmutable::parse($request->input('start'))->setTimezone(config('app.timezone')), report: false)
            : null;

        return view('admin.appointments.create', [
            ...$this->formData(),
            'appointment' => new Appointment([
                'starts_at' => $startsAt,
                'status' => AppointmentStatus::Confirmed,
                'client_id' => $request->integer('client_id') ?: null,
            ]),
        ]);
    }

    public function store(AppointmentRequest $request, AppointmentScheduler $scheduler): RedirectResponse
    {
        try {
            $scheduler->book(
                Client::findOrFail($request->integer('client_id')),
                $request->service(),
                $request->addons(),
                $request->startsAt(),
                [...$request->appointmentAttributes(), 'source' => 'admin'],
                enforceBusinessHours: ! $request->boolean('ignore_business_hours'),
            );
        } catch (SlotUnavailableException $exception) {
            return back()->withInput()->withErrors(['time' => $exception->getMessage()]);
        }

        return redirect()->route('admin.calendar')->with('status', 'Turno creado.');
    }

    public function edit(Appointment $appointment): View
    {
        $appointment->load(['client', 'addons']);

        return view('admin.appointments.edit', [
            ...$this->formData(),
            'appointment' => $appointment,
        ]);
    }

    public function update(AppointmentRequest $request, Appointment $appointment, AppointmentScheduler $scheduler): RedirectResponse
    {
        try {
            $scheduler->update(
                $appointment,
                $request->service(),
                $request->addons(),
                $request->startsAt(),
                $request->appointmentAttributes(),
                enforceBusinessHours: ! $request->boolean('ignore_business_hours'),
            );
        } catch (SlotUnavailableException $exception) {
            return back()->withInput()->withErrors(['time' => $exception->getMessage()]);
        }

        return redirect()->route('admin.appointments.edit', $appointment)->with('status', 'Turno actualizado.');
    }

    /**
     * @return array{clients: Collection<int, Client>, services: Collection<int, Service>, statuses: list<AppointmentStatus>}
     */
    private function formData(): array
    {
        return [
            'clients' => Client::query()->orderBy('name')->get(['id', 'name', 'phone']),
            'services' => Service::query()->with('addons')->orderBy('sort_order')->orderBy('name')->get(),
            'statuses' => AppointmentStatus::cases(),
        ];
    }
}
