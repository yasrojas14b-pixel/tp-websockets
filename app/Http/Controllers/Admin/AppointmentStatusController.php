<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Exceptions\SlotUnavailableException;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AppointmentScheduler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentStatusController extends Controller
{
    public function update(Request $request, Appointment $appointment, AppointmentScheduler $scheduler): RedirectResponse
    {
        $request->validate(['status' => ['required', Rule::enum(AppointmentStatus::class)]]);

        try {
            $scheduler->changeStatus($appointment, $request->enum('status', AppointmentStatus::class));
        } catch (SlotUnavailableException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('status', 'Estado actualizado a "'.$appointment->status->label().'".');
    }
}
