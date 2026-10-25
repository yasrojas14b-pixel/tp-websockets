<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\SlotUnavailableException;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AppointmentScheduler;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentScheduleController extends Controller
{
    /**
     * Drag & drop / resize from the admin calendar.
     */
    public function update(Request $request, Appointment $appointment, AppointmentScheduler $scheduler): JsonResponse
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $timezone = config('app.timezone');

        try {
            $scheduler->reschedule(
                $appointment,
                CarbonImmutable::parse($request->input('start'))->setTimezone($timezone),
                CarbonImmutable::parse($request->input('end'))->setTimezone($timezone),
            );
        } catch (SlotUnavailableException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }

        return response()->json(['message' => 'Turno movido.']);
    }
}
