<x-layouts.admin title="Servicios">
    <x-slot:actions>
        <div class="flex gap-2">
            <a href="{{ route('admin.addons.create') }}" class="btn btn-secondary">+ Adicional</a>
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary">+ Servicio</a>
        </div>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="card overflow-x-auto p-0 xl:col-span-2">
            <h2 class="px-5 pt-5 text-lg font-semibold">Catálogo</h2>
            <table class="table mt-3">
                <thead><tr><th>Servicio</th><th>Duración</th><th>Precio</th><th>Adicionales</th><th></th></tr></thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr @class(['opacity-50' => ! $service->is_active])>
                            <td>
                                <p class="font-medium">{{ $service->name }} @unless ($service->is_active)<span class="badge bg-stone-100 text-stone-500">Inactivo</span>@endunless</p>
                                <p class="text-xs text-stone-500">{{ \Illuminate\Support\Str::limit($service->description, 80) }}</p>
                            </td>
                            <td class="whitespace-nowrap">{{ $service->duration_label }}</td>
                            <td class="whitespace-nowrap">{{ \App\Support\Money::format($service->price) }}</td>
                            <td class="text-xs text-stone-500">{{ $service->addons->pluck('name')->join(', ') ?: '—' }}</td>
                            <td class="whitespace-nowrap text-right">
                                <a href="{{ route('admin.services.edit', $service) }}" class="text-pink-700 hover:underline">Editar</a>
                                <form method="POST" action="{{ route('admin.services.destroy', $service) }}" class="inline" onsubmit="return confirm('¿Eliminar este servicio?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ml-3 text-stone-400 hover:text-red-600">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-stone-500">Todavía no cargaste servicios.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="card h-fit">
            <h2 class="mb-1 text-lg font-semibold">Adicionales (add-ons)</h2>
            <p class="mb-4 text-xs text-stone-500">Se suman al precio y a la duración del servicio elegido.</p>
            <ul class="flex flex-col divide-y divide-nude-100">
                @forelse ($addons as $addon)
                    <li @class(['flex items-center justify-between gap-3 py-2.5 text-sm', 'opacity-50' => ! $addon->is_active])>
                        <div>
                            <p class="font-medium">{{ $addon->name }}</p>
                            <p class="text-xs text-stone-500">+{{ $addon->duration_label }} · +{{ \App\Support\Money::format($addon->price) }} · en {{ $addon->services_count }} servicio(s)</p>
                        </div>
                        <a href="{{ route('admin.addons.edit', $addon) }}" class="text-pink-700 hover:underline">Editar</a>
                    </li>
                @empty
                    <li class="py-4 text-sm text-stone-500">Sin adicionales.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-layouts.admin>
