<x-layouts.admin title="Hola, {{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }}">
    <x-slot:actions>
        <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">+ Nuevo turno</a>
    </x-slot:actions>

    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <a href="{{ route('admin.appointments.index', ['status' => 'pending']) }}" class="card hover:border-pink-300">
            <p class="text-sm text-stone-500">Por confirmar</p>
            <p class="mt-1 text-3xl font-bold text-amber-600">{{ $pendingCount }}</p>
        </a>
        <div class="card">
            <p class="text-sm text-stone-500">Turnos esta semana</p>
            <p class="mt-1 text-3xl font-bold">{{ $weekCount }}</p>
        </div>
        <div class="card">
            <p class="text-sm text-stone-500">Facturado este mes</p>
            <p class="mt-1 text-3xl font-bold text-emerald-700">{{ \App\Support\Money::format($monthRevenue) }}</p>
        </div>
        <a href="{{ route('admin.clients.index') }}" class="card hover:border-pink-300">
            <p class="text-sm text-stone-500">Clientas</p>
            <p class="mt-1 text-3xl font-bold">{{ $clientCount }}</p>
        </a>
    </div>

    <section class="card">
        <h2 class="mb-4 text-lg font-semibold">Hoy, {{ now()->translatedFormat('l j \d\e F') }}</h2>

        @forelse ($todayAppointments as $appointment)
            <div class="flex flex-wrap items-center gap-4 border-b border-nude-100 py-3 last:border-0">
                <div class="w-24 shrink-0 font-semibold">{{ $appointment->starts_at->format('H:i') }}–{{ $appointment->ends_at->format('H:i') }}</div>
                <div class="min-w-0 flex-1">
                    <a href="{{ route('admin.clients.show', $appointment->client) }}" class="font-medium hover:text-pink-700">{{ $appointment->client->name }}</a>
                    <p class="text-sm text-stone-500">
                        {{ $appointment->service->name }}@if ($appointment->addons->isNotEmpty()) + {{ $appointment->addons->pluck('name')->join(', ') }}@endif
                    </p>
                </div>
                <x-status-badge :status="$appointment->status" />
                <div class="flex gap-2">
                    @include('admin.appointments._status-actions', ['appointment' => $appointment])
                    <a href="{{ $appointment->client->whatsapp_url }}" target="_blank" rel="noopener" class="btn btn-secondary py-1.5">WhatsApp</a>
                </div>
            </div>
        @empty
            <p class="text-stone-500">No hay turnos para hoy.</p>
        @endforelse
    </section>
</x-layouts.admin>
