<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="name" class="label">Nombre y apellido</label>
        <input id="name" name="name" class="input" required maxlength="120" value="{{ old('name', $client->name) }}">
    </div>
    <div>
        <label for="phone" class="label">Teléfono / WhatsApp</label>
        <input id="phone" name="phone" type="tel" class="input" required value="{{ old('phone', $client->phone) }}" placeholder="5491123456789">
        <p class="mt-1 text-xs text-stone-500">Con código de país y área, sin 0 ni 15 (ej: 54 9 11...).</p>
    </div>
    <div>
        <label for="email" class="label">Email <span class="font-normal text-stone-400">(opcional)</span></label>
        <input id="email" name="email" type="email" class="input" value="{{ old('email', $client->email) }}">
    </div>
    <div>
        <label for="nail_type" class="label">Tipo de uña</label>
        <input id="nail_type" name="nail_type" class="input" value="{{ old('nail_type', $client->nail_type) }}" placeholder="Ej: finas, se quiebran, cutícula seca...">
    </div>
    <div class="md:col-span-2">
        <label for="allergies" class="label">Alergias / sensibilidades</label>
        <textarea id="allergies" name="allergies" rows="2" class="input" placeholder="Ej: alergia al HEMA, sensibilidad a la acetona...">{{ old('allergies', $client->allergies) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label for="preferred_designs" class="label">Diseños y colores preferidos</label>
        <textarea id="preferred_designs" name="preferred_designs" rows="2" class="input">{{ old('preferred_designs', $client->preferred_designs) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label for="internal_notes" class="label">Nota interna</label>
        <textarea id="internal_notes" name="internal_notes" rows="4" class="input" placeholder="Solo la ves vos.">{{ old('internal_notes', $client->internal_notes) }}</textarea>
    </div>
</div>
