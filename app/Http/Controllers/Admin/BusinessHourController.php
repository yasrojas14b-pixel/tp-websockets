<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use App\Services\AppointmentScheduler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

class BusinessHourController extends Controller
{
    public function edit(): View
    {
        return view('admin.hours.edit', [
            'hoursByWeekday' => BusinessHour::query()->orderBy('opens_at')->get()->groupBy('weekday'),
        ]);
    }

    /**
     * Each weekday has up to two ranges (morning / afternoon) so a daily
     * lunch break is simply the gap between them.
     */
    public function update(Request $request, AppointmentScheduler $scheduler): RedirectResponse
    {
        $validator = validator($request->all(), [
            'hours' => ['array'],
            'hours.*.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.*.closes_at' => ['nullable', 'date_format:H:i', 'required_with:hours.*.*.opens_at'],
        ])->after(function (Validator $validator) use ($request): void {
            foreach ($request->input('hours', []) as $weekday => $ranges) {
                foreach ($ranges as $index => $range) {
                    if (filled($range['opens_at'] ?? null) && filled($range['closes_at'] ?? null) && $range['closes_at'] <= $range['opens_at']) {
                        $validator->errors()->add("hours.{$weekday}.{$index}.closes_at", BusinessHour::WEEKDAY_NAMES[$weekday].': el cierre debe ser posterior a la apertura.');
                    }
                }
            }
        });

        $validator->validate();

        DB::transaction(function () use ($request): void {
            BusinessHour::query()->delete();

            foreach ($request->input('hours', []) as $weekday => $ranges) {
                foreach ($ranges as $range) {
                    if (filled($range['opens_at'] ?? null) && filled($range['closes_at'] ?? null)) {
                        BusinessHour::create(['weekday' => (int) $weekday, ...$range]);
                    }
                }
            }
        });

        $scheduler->announce(now());

        return back()->with('status', 'Horarios de atención guardados.');
    }
}
