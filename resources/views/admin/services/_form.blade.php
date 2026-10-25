@php
    $selectedAddonIds = array_map('intval', old('addons', $service->exists ? $service->addons->pluck('id')->all() : []));
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="name" class="label">Nombre</label>
        <input id="name" name="name" class="input" required maxlength="120" value="{{ old('name', $service->name) }}" placeholder="Ej: Kapping en gel">
    </div>
    <div class="md:col-span-2">
        <label for="description" class="label">Descripción</label>
        <textarea id="description" name="description" rows="3" class="input">{{ old('description', $service->description) }}</textarea>
    </div>
    <div>
        <label for="price" class="label">Precio ($)</label>
        <input id="price" name="price" type="number" min="0" step="1" class="input" required value="{{ old('price', $service->price) }}">
    </div>
    <div>
        <label for="duration_minutes" class="label">Duración estimada (minutos)</label>
        <input id="duration_minutes" name="duration_minutes" type="number" min="5" step="5" class="input" required value="{{ old('duration_minutes', $service->duration_minutes) }}" placeholder="90 = 1h 30m">
    </div>
    <div>
        <label for="sort_order" class="label">Orden en el catálogo</label>
        <input id="sort_order" name="sort_order" type="number" min="0" class="input" value="{{ old('sort_order', $service->sort_order ?? 0) }}">
    </div>
    <div class="flex items-end">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" class="rounded text-pink-600" @checked(old('is_active', $service->is_active))>
            Visible para reservas online
        </label>
    </div>

    @if ($addons->isNotEmpty())
        <fieldset class="md:col-span-2">
            <legend class="label">Adicionales que se pueden sumar</legend>
            <div class="flex flex-wrap gap-2">
                @foreach ($addons as $addon)
                    <label class="flex cursor-pointer items-center gap-2 rounded-full border border-nude-300 px-3 py-1.5 text-sm has-checked:border-pink-500 has-checked:bg-pink-50">
                        <input type="checkbox" name="addons[]" value="{{ $addon->id }}" class="rounded text-pink-600" @checked(in_array($addon->id, $selectedAddonIds, true))>
                        {{ $addon->name }}
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif
</div>
