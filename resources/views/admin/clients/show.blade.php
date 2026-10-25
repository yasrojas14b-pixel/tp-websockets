<x-layouts.admin :title="$client->name">
    <x-slot:actions>
        <div class="flex flex-wrap gap-2">
            <a href="{{ $client->whatsapp_url }}" target="_blank" rel="noopener" class="btn btn-secondary">WhatsApp</a>
            <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-secondary">Editar ficha</a>
            <a href="{{ route('admin.appointments.create', ['client_id' => $client->id]) }}" class="btn btn-primary">+ Turno</a>
        </div>
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6">
            <section class="card text-sm">
                <h2 class="mb-3 text-lg font-semibold">Ficha</h2>
                <dl class="flex flex-col gap-3">
                    <div><dt class="text-stone-500">Teléfono</dt><dd class="font-medium">{{ $client->phone }}</dd></div>
                    @if ($client->email)
                        <div><dt class="text-stone-500">Email</dt><dd>{{ $client->email }}</dd></div>
                    @endif
                    <div><dt class="text-stone-500">Tipo de uña</dt><dd>{{ $client->nail_type ?: '—' }}</dd></div>
                    <div>
                        <dt class="text-stone-500">Alergias</dt>
                        <dd @class(['rounded-lg bg-red-50 px-2 py-1 font-medium text-red-800' => $client->allergies])>{{ $client->allergies ?: 'Ninguna registrada' }}</dd>
                    </div>
                    <div><dt class="text-stone-500">Diseños preferidos</dt><dd>{{ $client->preferred_designs ?: '—' }}</dd></div>
                    <div><dt class="text-stone-500">Nota interna</dt><dd class="whitespace-pre-line">{{ $client->internal_notes ?: '—' }}</dd></div>
                </dl>
            </section>

            <section class="card">
                <h2 class="mb-3 text-lg font-semibold">Fotos de referencia</h2>

                @if ($client->photos->isNotEmpty())
                    <div class="mb-4 grid grid-cols-3 gap-2">
                        @foreach ($client->photos as $photo)
                            <figure class="group relative">
                                <a href="{{ $photo->url }}" target="_blank" rel="noopener">
                                    <img src="{{ $photo->url }}" alt="{{ $photo->caption ?: 'Foto de referencia' }}" class="aspect-square w-full rounded-lg object-cover">
                                </a>
                                @if ($photo->caption)
                                    <figcaption class="mt-1 truncate text-xs text-stone-500">{{ $photo->caption }}</figcaption>
                                @endif
                                <form method="POST" action="{{ route('admin.clients.photos.destroy', [$client, $photo]) }}" class="absolute top-1 right-1" onsubmit="return confirm('¿Eliminar foto?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-full bg-white/90 px-2 text-xs text-red-600 shadow" aria-label="Eliminar foto">✕</button>
                                </form>
                            </figure>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.clients.photos.store', $client) }}" enctype="multipart/form-data" class="flex flex-col gap-2">
                    @csrf
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required class="text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-pink-50 file:px-3 file:py-2 file:text-pink-700">
                    <input type="text" name="caption" class="input" placeholder="Descripción (opcional)" maxlength="255">
                    <button class="btn btn-secondary">Subir foto</button>
                </form>
            </section>
        </div>

        <section class="card overflow-x-auto p-0 lg:col-span-2">
            <h2 class="px-5 pt-5 text-lg font-semibold">Historial de turnos ({{ $client->appointments->count() }})</h2>
            <table class="table mt-3">
                <thead><tr><th>Fecha</th><th>Servicio</th><th>Total</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                    @forelse ($client->appointments as $appointment)
                        <tr>
                            <td class="whitespace-nowrap">{{ $appointment->starts_at->format('d/m/Y H:i') }}</td>
                            <td>
                                {{ $appointment->service->name }}
                                @if ($appointment->addons->isNotEmpty())
                                    <span class="block text-xs text-stone-500">+ {{ $appointment->addons->pluck('name')->join(', ') }}</span>
                                @endif
                                @if ($appointment->admin_notes)
                                    <span class="block text-xs text-stone-500 italic">{{ $appointment->admin_notes }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">{{ \App\Support\Money::format($appointment->total_price) }}</td>
                            <td><x-status-badge :status="$appointment->status" /></td>
                            <td class="text-right"><a href="{{ route('admin.appointments.edit', $appointment) }}" class="text-pink-700 hover:underline">Abrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-stone-500">Todavía no tiene turnos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
</x-layouts.admin>
