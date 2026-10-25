<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Service;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AvailabilityController extends Controller
{
    /**
     * Free start times for a service (+ add-ons) on one day.
     *
     * GET /disponibilidad?service_id=3&addons[]=1&date=2026-10-24
     */
    public function __invoke(Request $request, AvailabilityService $availability): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', Rule::exists('services', 'id')->where('is_active', true)],
            'addons' => ['array'],
            'addons.*' => ['integer'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $addonMinutes = Addon::query()
            ->active()
            ->whereKey($validated['addons'] ?? [])
            ->whereRelation('services', 'services.id', $service->id)
            ->sum('duration_minutes');

        $duration = $service->duration_minutes + (int) $addonMinutes;
        $date = CarbonImmutable::createFromFormat('Y-m-d', $validated['date']);

        return response()->json([
            'date' => $date->toDateString(),
            'duration_minutes' => $duration,
            'slots' => $availability->slotsFor($date, $duration)->values(),
        ]);
    }
}
