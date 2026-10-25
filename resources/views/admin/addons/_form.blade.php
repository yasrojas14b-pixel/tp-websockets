@php
    $selectedServiceIds = array_map('intval', old('services', $addon->exists ? $addon->services->pluck('id')->all() : []));
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="name" class="label">Nombre</label>
        <input id="name" name="name" class="input" required maxlength="120" value="{{ old('name', $addon->name) }}" placeholder="Ej: Retirado de material anterior">
    </div>
    <div class="md:col-span-2">
        <label for="description" class="label">Descripción</label>
        <textarea id="description" name="description" rows="2" class="input">{{ old('description', $addon->description) }}</textarea>
    </div>
    <div>
        <label for="price" class="label">Precio adicional ($)</label>
        <input id="price" name="price" type="number" min="0" step="1" class="input" required value="{{ old('price', $addon->price) }}">
    </div>
    <div>
        <label for="duration_minutes" class="label">Tiempo adicional (minutos)</label>
        <input id="duration_minutes" name="duration_minutes" type="number" min="0" step="5" class="input" required value="{{ old('duration_minutes', $addon->duration_minutes ?? 0) }}">
    </div>
    <div class="md:col-span-2">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" class="rounded text-pink-600" @checked(old('is_active', $addon->is_active))>
            Activo
        </label>
    </div>
    <fieldset class="md:col-span-2">
        <legend class="label">Disponible para</legend>
        <div class="flex flex-wrap gap-2">
            @foreach ($services as $service)
                <label class="flex cursor-pointer items-center gap-2 rounded-full border border-nude-300 px-3 py-1.5 text-sm has-checked:border-pink-500 has-checked:bg-pink-50">
                    <input type="checkbox" name="services[]" value="{{ $service->id }}" class="rounded text-pink-600" @checked(in_array($service->id, $selectedServiceIds, true))>
                    {{ $service->name }}
                </label>
            @endforeach
        </div>
    </fieldset>
</div>
