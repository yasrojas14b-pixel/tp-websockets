<x-layouts.admin title="Turno de {{ $appointment->client->name }}">
    <x-slot:actions>
        <div class="flex flex-wrap items-center gap-2">
            <x-status-badge :status="$appointment->status" />
            @include('admin.appointments._status-actions')
            <a href="{{ $appointment->client->whatsapp_url }}" target="_blank" rel="noopener" class="btn btn-secondary py-1.5">WhatsApp</a>
        </div>
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin.appointments.update', $appointment) }}" class="card lg:col-span-2">
            @csrf
            @method('PUT')
            @include('admin.appointments._form')

            <div class="mt-6 flex gap-3">
                <button class="btn btn-primary">Guardar cambios</button>
                <a href="{{ route('admin.calendar') }}" class="btn btn-secondary">Volver a la agenda</a>
            </div>
        </form>

        <aside class="card h-fit text-sm">
            <h2 class="mb-3 font-semibold">Resumen</h2>
            <dl class="flex flex-col gap-2">
                <div class="flex justify-between"><dt class="text-stone-500">Horario</dt><dd>{{ $appointment->starts_at->format('d/m H:i') }}–{{ $appointment->ends_at->format('H:i') }}</dd></div>
                <div class="flex justify-between"><dt class="text-stone-500">Total</dt><dd class="font-semibold">{{ \App\Support\Money::format($appointment->total_price) }}</dd></div>
                <div class="flex justify-between"><dt class="text-stone-500">Origen</dt><dd>{{ $appointment->source === 'online' ? 'Reserva online' : 'Cargado por el salón' }}</dd></div>
                <div class="flex justify-between"><dt class="text-stone-500">Código</dt><dd class="font-mono text-xs">{{ $appointment->public_code }}</dd></div>
            </dl>

            @if ($appointment->client->allergies)
                <p class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-red-800"><strong>Alergias:</strong> {{ $appointment->client->allergies }}</p>
            @endif

            <a href="{{ route('admin.clients.show', $appointment->client) }}" class="mt-4 inline-block text-pink-700 hover:underline">Ver ficha de la clienta →</a>
        </aside>
    </div>
</x-layouts.admin>
