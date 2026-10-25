<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ScheduleBlock;
use App\Services\AppointmentScheduler;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleBlockController extends Controller
{
    public function index(): View
    {
        return view('admin.blocks.index', [
            'blocks' => ScheduleBlock::query()->where('ends_at', '>=', now())->orderBy('starts_at')->get(),
        ]);
    }

    /**
     * Existing appointments are not cancelled automatically; the admin is
     * warned so she can contact those clients.
     */
    public function store(Request $request, AppointmentScheduler $scheduler): RedirectResponse
    {
        $validated = $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'reason' => ['nullable', 'string', 'max:255'],
        ], attributes: ['starts_at' => 'desde', 'ends_at' => 'hasta', 'reason' => 'motivo']);

        $block = ScheduleBlock::create($validated);

        $affectedCount = Appointment::query()->occupying()->overlapping($block->starts_at, $block->ends_at)->count();
        $scheduler->announce(...CarbonImmutable::instance($block->starts_at)->daysUntil($block->ends_at));

        $message = 'Horario bloqueado.';

        if ($affectedCount > 0) {
            $message .= " Atención: hay {$affectedCount} turno(s) activos en ese rango que tenés que reprogramar o cancelar.";
        }

        return back()->with('status', $message);
    }

    public function destroy(ScheduleBlock $block, AppointmentScheduler $scheduler): RedirectResponse
    {
        $block->delete();
        $scheduler->announce(...CarbonImmutable::instance($block->starts_at)->daysUntil($block->ends_at));

        return back()->with('status', 'Bloqueo eliminado.');
    }
}
