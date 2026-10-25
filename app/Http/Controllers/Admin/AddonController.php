<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AddonController extends Controller
{
    public function create(): View
    {
        return view('admin.addons.create', [
            'addon' => new Addon(['is_active' => true]),
            'services' => Service::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $addon = Addon::create($validated);
        $addon->services()->sync($validated['services'] ?? []);

        return redirect()->route('admin.services.index')->with('status', 'Adicional creado.');
    }

    public function edit(Addon $addon): View
    {
        return view('admin.addons.edit', [
            'addon' => $addon->load('services'),
            'services' => Service::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Addon $addon): RedirectResponse
    {
        $validated = $this->validated($request);

        $addon->update($validated);
        $addon->services()->sync($validated['services'] ?? []);

        return redirect()->route('admin.services.index')->with('status', 'Adicional actualizado.');
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
                'duration_minutes' => ['required', 'integer', 'min:0', 'max:300'],
                'services' => ['array'],
                'services.*' => ['integer', Rule::exists('services', 'id')],
            ], attributes: ['name' => 'nombre', 'price' => 'precio', 'duration_minutes' => 'duración']),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
