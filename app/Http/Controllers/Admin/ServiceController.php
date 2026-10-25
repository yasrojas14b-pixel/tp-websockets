<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', [
            'services' => Service::query()->with('addons')->withCount('appointments')->orderBy('sort_order')->orderBy('name')->get(),
            'addons' => Addon::query()->withCount('services')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create', [
            'service' => new Service(['is_active' => true]),
            'addons' => Addon::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $service = Service::create($validated);
        $service->addons()->sync($validated['addons'] ?? []);

        return redirect()->route('admin.services.index')->with('status', 'Servicio creado.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', [
            'service' => $service->load('addons'),
            'addons' => Addon::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $this->validated($request);

        $service->update($validated);
        $service->addons()->sync($validated['addons'] ?? []);

        return redirect()->route('admin.services.index')->with('status', 'Servicio actualizado.');
    }

    /**
     * Services with appointment history are deactivated instead of deleted.
     */
    public function destroy(Service $service): RedirectResponse
    {
        if ($service->appointments()->exists()) {
            $service->update(['is_active' => false]);

            return back()->with('status', 'El servicio tiene turnos registrados, así que se desactivó en lugar de borrarse.');
        }

        $service->delete();

        return back()->with('status', 'Servicio eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return [
            ...$request->validate([
                'name' => ['required', 'string', 'max:120'],
                'description' => ['nullable', 'string', 'max:1000'],
                'price' => ['required', 'numeric', 'min:0'],
                'duration_minutes' => ['required', 'integer', 'min:5', 'max:600'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'addons' => ['array'],
                'addons.*' => ['integer', Rule::exists('addons', 'id')],
            ], attributes: ['name' => 'nombre', 'price' => 'precio', 'duration_minutes' => 'duración']),
            'sort_order' => $request->integer('sort_order'),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
