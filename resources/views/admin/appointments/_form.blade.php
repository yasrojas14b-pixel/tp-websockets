@php
    $selectedServiceId = (int) old('service_id', $appointment->service_id);
    $selectedAddonIds = array_map('intval', old('addons', $appointment->exists ? $appointment->addons->pluck('id')->all() : []));
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="client_id" class="label">Clienta</label>
        <div class="flex gap-2">
            <select id="client_id" name="client_id" class="input" required>
                <option value="">Elegí una clienta…</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected((int) old('client_id', $appointment->client_id) === $client->id)>{{ $client->name }} · {{ $client->phone }}</option>
                @endforeach
            </select>
            <a href="{{ route('admin.clients.create') }}" class="btn btn-secondary shrink-0">+ Nueva</a>
        </div>
    </div>

    <div class="md:col-span-2">
        <label for="service_id" class="label">Servicio</label>
        <select id="service_id" name="service_id" class="input" required>
            <option value="">Elegí un servicio…</option>
            @foreach ($services as $service)
                <option value="{{ $service->id }}" @selected($selectedServiceId === $service->id)>
                    {{ $service->name }} · {{ $service->duration_label }} · {{ \App\Support\Money::format($service->price) }}{{ $service->is_active ? '' : ' (inactivo)' }}
                </option>
            @endforeach
        </select>
    </div>

    @foreach ($services as $service)
        @if ($service->addons->isNotEmpty())
            <fieldset data-addons-for="{{ $service->id }}" @class(['md:col-span-2', 'hidden' => $selectedServiceId !== $service->id])>
                <legend class="label">Adicionales</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach ($service->addons as $addon)
                        <label class="flex cursor-pointer items-center gap-2 rounded-full border border-nude-300 px-3 py-1.5 text-sm has-checked:border-pink-500 has-checked:bg-pink-50">
                            <input type="checkbox" name="addons[]" value="{{ $addon->id }}" class="rounded text-pink-600"
                                   @checked($selectedServiceId === $service->id && in_array($addon->id, $selectedAddonIds, true))
                                   @disabled($selectedServiceId !== $service->id)>
                            {{ $addon->name }} (+{{ $addon->duration_label }}, +{{ \App\Support\Money::format($addon->price) }})
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endif
    @endforeach

    <div>
        <label for="date" class="label">Fecha</label>
        <input id="date" name="date" type="date" class="input" required value="{{ old('date', $appointment->starts_at?->toDateString()) }}">
    </div>
    <div>
        <label for="time" class="label">Hora de inicio</label>
        <input id="time" name="time" type="time" step="300" class="input" required value="{{ old('time', $appointment->starts_at?->format('H:i')) }}">
        <p class="mt-1 text-xs text-stone-500">La hora de fin se calcula sola según el servicio y los adicionales.</p>
    </div>

    <div>
        <label for="status" class="label">Estado</label>
        <select id="status" name="status" class="input">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $appointment->status?->value) === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-end">
        <label class="flex items-center gap-2 text-sm text-stone-600">
            <input type="hidden" name="ignore_business_hours" value="0">
            <input type="checkbox" name="ignore_business_hours" value="1" class="rounded text-pink-600" @checked(old('ignore_business_hours'))>
            Permitir fuera del horario de atención
        </label>
    </div>

    <div class="md:col-span-2">
        <label for="client_notes" class="label">Comentario de la clienta</label>
        <textarea id="client_notes" name="client_notes" rows="2" class="input">{{ old('client_notes', $appointment->client_notes) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label for="admin_notes" class="label">Nota interna del turno</label>
        <textarea id="admin_notes" name="admin_notes" rows="3" class="input" placeholder="Ej: diseño de referencia, color elegido, seña recibida...">{{ old('admin_notes', $appointment->admin_notes) }}</textarea>
    </div>
</div>

<script>
    document.getElementById('service_id').addEventListener('change', (event) => {
        document.querySelectorAll('[data-addons-for]').forEach((fieldset) => {
            const isCurrent = fieldset.dataset.addonsFor === event.target.value;
            fieldset.classList.toggle('hidden', ! isCurrent);
            fieldset.querySelectorAll('input').forEach((input) => {
                input.disabled = ! isCurrent;
                if (! isCurrent) {
                    input.checked = false;
                }
            });
        });
    });
</script>
