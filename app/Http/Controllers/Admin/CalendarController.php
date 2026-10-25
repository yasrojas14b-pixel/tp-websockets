<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BusinessHour;
use App\Models\ScheduleBlock;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        $businessHours = BusinessHour::query()->orderBy('weekday')->orderBy('opens_at')->get()
            ->map(fn (BusinessHour $hours): array => [
                'daysOfWeek' => [$hours->weekday],
                'startTime' => substr($hours->opens_at, 0, 5),
                'endTime' => substr($hours->closes_at, 0, 5),
            ]);

        return view('admin.calendar', ['businessHours' => $businessHours]);
    }

    /**
     * Event feed for FullCalendar. It requests ?start=...&end=... in ISO 8601.
     */
    public function events(Request $request): JsonResponse
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
        ]);

        $start = CarbonImmutable::parse($request->input('start'))->setTimezone(config('app.timezone'));
        $end = CarbonImmutable::parse($request->input('end'))->setTimezone(config('app.timezone'));

        $appointments = Appointment::query()
            ->with(['client:id,name,phone', 'service:id,name'])
            ->overlapping($start, $end)
            ->get()
            ->map(fn (Appointment $appointment): array => [
                'id' => 'appointment-'.$appointment->id,
                'title' => $appointment->client->name.' · '.$appointment->service->name,
                'start' => $appointment->starts_at->toIso8601String(),
                'end' => $appointment->ends_at->toIso8601String(),
                'color' => $appointment->status->color(),
                'editable' => $appointment->status !== AppointmentStatus::Completed,
                'extendedProps' => [
                    'type' => 'appointment',
                    'status' => $appointment->status->label(),
                    'updateUrl' => route('admin.appointments.schedule', $appointment),
                    'editUrl' => route('admin.appointments.edit', $appointment),
                ],
            ]);

        $blocks = ScheduleBlock::query()
            ->overlapping($start, $end)
            ->get()
            ->map(fn (ScheduleBlock $block): array => [
                'id' => 'block-'.$block->id,
                'title' => 'Bloqueado'.($block->reason ? ': '.$block->reason : ''),
                'start' => $block->starts_at->toIso8601String(),
                'end' => $block->ends_at->toIso8601String(),
                'color' => '#6b7280',
                'editable' => false,
                'extendedProps' => ['type' => 'block'],
            ]);

        return response()->json($appointments->toBase()->merge($blocks)->values());
    }
}
