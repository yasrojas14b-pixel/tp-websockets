<x-layouts.admin title="Turnos">
    <x-slot:actions>
        <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">+ Nuevo turno</a>
    </x-slot:actions>

    <form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
        <div>
            <label for="status" class="label">Estado</label>
            <select id="status" name="status" class="input">
                <option value="">Todos</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="date" class="label">Día</label>
            <input id="date" name="date" type="date" class="input" value="{{ $filters['date'] ?? '' }}">
        </div>
        <button class="btn btn-secondary">Filtrar</button>
        @if ($filters)
            <a href="{{ route('admin.appointments.index') }}" class="btn text-stone-500">Limpiar</a>
        @endif
    </form>

    <div class="card overflow-x-auto p-0">
        <table class="table">
            <thead>
                <tr><th>Fecha</th><th>Clienta</th><th>Servicio</th><th>Total</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($appointments as $appointment)
                    <tr>
                        <td class="whitespace-nowrap">
                            <span class="font-medium">{{ ucfirst($appointment->starts_at->translatedFormat('D d/m')) }}</span><br>
                            <span class="text-stone-500">{{ $appointment->starts_at->format('H:i') }}–{{ $appointment->ends_at->format('H:i') }}</span>
                        </td>
                        <td><a href="{{ route('admin.clients.show', $appointment->client) }}" class="hover:text-pink-700">{{ $appointment->client->name }}</a></td>
                        <td>{{ $appointment->service->name }}</td>
                        <td class="whitespace-nowrap">{{ \App\Support\Money::format($appointment->total_price) }}</td>
                        <td><x-status-badge :status="$appointment->status" /></td>
                        <td class="text-right"><a href="{{ route('admin.appointments.edit', $appointment) }}" class="text-pink-700 hover:underline">Abrir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-stone-500">No hay turnos con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $appointments->links() }}</div>
</x-layouts.admin>
