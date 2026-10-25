<x-layouts.public title="Turno reservado">
    @php
        $whatsappText = rawurlencode("Hola! Reservé un turno de {$appointment->service->name} para el {$appointment->starts_at->translatedFormat('l j \d\e F')} a las {$appointment->starts_at->format('H:i')}. Código: {$appointment->public_code}");
    @endphp

    <div class="card text-center">
        <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-pink-100 text-pink-700">
            <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
        </div>

        <h1 class="font-display text-2xl font-bold">¡Listo, {{ \Illuminate\Support\Str::of($appointment->client->name)->before(' ') }}!</h1>
        <p class="mt-1 text-stone-500">Tu turno quedó reservado.</p>

        <div class="mt-4">
    <x-status-badge
        id="appointment-status"
        :status="$appointment->status"
    />
</div>
        <dl class="mt-6 divide-y divide-nude-100 text-left text-sm">
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-stone-500">Servicio</dt>
                <dd class="text-right font-medium">
                    {{ $appointment->service->name }}
                    @foreach ($appointment->addons as $addon)
                        <span class="block text-xs font-normal text-stone-500">+ {{ $addon->name }}</span>
                    @endforeach
                </dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-stone-500">Día</dt>
                <dd class="font-medium">{{ ucfirst($appointment->starts_at->translatedFormat('l j \d\e F')) }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-stone-500">Horario</dt>
                <dd class="font-medium">{{ $appointment->starts_at->format('H:i') }} a {{ $appointment->ends_at->format('H:i') }} hs</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-stone-500">Total estimado</dt>
                <dd class="font-semibold text-pink-700">{{ \App\Support\Money::format($appointment->total_price) }}</dd>
            </div>
        </dl>

        @if ($appointment->status === \App\Enums\AppointmentStatus::Pending)
    <p
        id="pending-notice"
        class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800"
    >
        Te vamos a escribir por WhatsApp para confirmarlo.
        Si querés, podés avisarnos ahora:
    </p>
@endif

        <div class="mt-6 flex flex-col gap-3">
            <a href="https://wa.me/{{ config('salon.whatsapp') }}?text={{ $whatsappText }}" class="btn btn-primary" target="_blank" rel="noopener">Avisar por WhatsApp</a>
            <a href="{{ route('booking.create') }}" class="btn btn-secondary">Reservar otro turno</a>
        </div>

        <p class="mt-6 text-xs text-stone-400">Guardá este link para ver el estado de tu turno.</p>
    </div>
    <x-slot:scripts>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusBadge = document.getElementById('appointment-status');
            const pendingNotice = document.getElementById('pending-notice');

            if (!window.Echo || !statusBadge) {
                console.error('Echo no está disponible para actualizar el turno.');
                return;
            }

            window.Echo
                .channel('appointment-status.{{ $appointment->public_code }}')
                .listen('.AppointmentStatusUpdated', (event) => {
                    statusBadge.textContent = event.label;
                    statusBadge.className = 'badge ' + event.badgeClasses;

                    if (pendingNotice && event.status !== 'pending') {
                        pendingNotice.remove();
                    }
                });
        });
    </script>
</x-slot:scripts>
</x-layouts.public>
