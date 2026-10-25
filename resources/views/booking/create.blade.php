<x-layouts.public title="Reservá tu turno">
    <form id="booking-form" method="POST" action="{{ route('booking.store') }}" class="flex flex-col gap-6" novalidate>
        @csrf

        <x-flash />

        {{-- Paso 1: servicio --}}
        <section class="card" aria-labelledby="step-service">
            <h2 id="step-service" class="mb-4 flex items-center gap-3 text-lg font-semibold">
                <span class="flex size-7 items-center justify-center rounded-full bg-pink-600 text-sm text-white">1</span>
                Elegí tu servicio
            </h2>

            <div class="flex flex-col gap-3">
                @foreach ($services as $service)
                    <label class="group relative flex cursor-pointer gap-4 rounded-xl border border-nude-200 p-4 transition hover:border-pink-300 has-checked:border-pink-500 has-checked:bg-pink-50 has-checked:ring-2 has-checked:ring-pink-200">
                        <input type="radio" name="service_id" value="{{ $service->id }}" class="sr-only"
                               data-price="{{ $service->price }}" data-duration="{{ $service->duration_minutes }}" data-name="{{ $service->name }}"
                               @checked(old('service_id') == $service->id)>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <p class="font-semibold text-stone-900">{{ $service->name }}</p>
                                <p class="shrink-0 font-semibold text-pink-700">{{ \App\Support\Money::format($service->price) }}</p>
                            </div>
                            @if ($service->description)
                                <p class="mt-1 text-sm text-stone-500">{{ $service->description }}</p>
                            @endif
                            <p class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-stone-500">
                                <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .2.08.39.22.53l3 3a.75.75 0 1 0 1.06-1.06l-2.78-2.78V5Z" clip-rule="evenodd"/></svg>
                                {{ $service->duration_label }}
                            </p>
                        </div>
                    </label>
                @endforeach
            </div>

            {{-- Adicionales del servicio elegido --}}
            @foreach ($services as $service)
                @if ($service->addons->isNotEmpty())
                    <fieldset data-addons-for="{{ $service->id }}" class="mt-5 hidden">
                        <legend class="mb-2 text-sm font-semibold text-stone-700">¿Querés sumar algo?</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($service->addons as $addon)
                                <label class="cursor-pointer rounded-full border border-nude-300 px-3 py-1.5 text-sm transition has-checked:border-pink-500 has-checked:bg-pink-600 has-checked:text-white">
                                    <input type="checkbox" name="addons[]" value="{{ $addon->id }}" class="sr-only" disabled
                                           data-price="{{ $addon->price }}" data-duration="{{ $addon->duration_minutes }}"
                                           @checked(in_array($addon->id, old('addons', [])))>
                                    {{ $addon->name }} · +{{ \App\Support\Money::format($addon->price) }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif
            @endforeach
        </section>

        {{-- Paso 2: fecha --}}
        <section class="card" aria-labelledby="step-date">
            <h2 id="step-date" class="mb-4 flex items-center gap-3 text-lg font-semibold">
                <span class="flex size-7 items-center justify-center rounded-full bg-pink-600 text-sm text-white">2</span>
                Elegí el día
            </h2>

            <div class="-mx-5 flex snap-x gap-2 overflow-x-auto px-5 pb-2" id="date-strip">
                @foreach ($days as $day)
                    <label @class([
                        'flex w-16 shrink-0 snap-start flex-col items-center rounded-xl border py-2 text-center transition',
                        'cursor-pointer border-nude-200 hover:border-pink-300 has-checked:border-pink-600 has-checked:bg-pink-600 has-checked:text-white' => $day['isOpen'],
                        'cursor-not-allowed border-transparent bg-stone-100 text-stone-400' => ! $day['isOpen'],
                    ])>
                        <input type="radio" name="date" value="{{ $day['value'] }}" class="sr-only" @disabled(! $day['isOpen']) @checked(old('date') === $day['value'])>
                        <span class="text-xs font-medium uppercase">{{ $day['weekday'] }}</span>
                        <span class="text-xl font-bold">{{ $day['day'] }}</span>
                        <span class="text-xs">{{ $day['month'] }}</span>
                    </label>
                @endforeach
            </div>
        </section>

        {{-- Paso 3: horario --}}
        <section class="card" aria-labelledby="step-time">
            <h2 id="step-time" class="mb-4 flex items-center gap-3 text-lg font-semibold">
                <span class="flex size-7 items-center justify-center rounded-full bg-pink-600 text-sm text-white">3</span>
                Elegí el horario
            </h2>

            <p id="slots-message" class="text-sm text-stone-500">Primero elegí un servicio y un día.</p>
            <div id="slots" class="grid grid-cols-3 gap-2 sm:grid-cols-4" role="radiogroup" aria-labelledby="step-time"></div>
            <p id="slots-live-notice" class="mt-3 hidden rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800" role="status"></p>
        </section>

        {{-- Paso 4: datos --}}
        <section class="card" aria-labelledby="step-data">
            <h2 id="step-data" class="mb-4 flex items-center gap-3 text-lg font-semibold">
                <span class="flex size-7 items-center justify-center rounded-full bg-pink-600 text-sm text-white">4</span>
                Tus datos
            </h2>

            <div class="flex flex-col gap-4">
                <div>
                    <label for="name" class="label">Nombre y apellido</label>
                    <input id="name" name="name" type="text" class="input" autocomplete="name" required maxlength="120" value="{{ old('name') }}">
                </div>
                <div>
                    <label for="phone" class="label">WhatsApp</label>
                    <input id="phone" name="phone" type="tel" class="input" autocomplete="tel" inputmode="tel" placeholder="11 2345 6789" required value="{{ old('phone') }}">
                    <p class="mt-1 text-xs text-stone-500">Te escribimos por acá para confirmar el turno.</p>
                </div>
                <div>
                    <label for="client_notes" class="label">¿Algo que debamos saber? <span class="font-normal text-stone-400">(opcional)</span></label>
                    <textarea id="client_notes" name="client_notes" rows="3" class="input" maxlength="500" placeholder="Ej: tengo esculpidas de otro salón, quiero un diseño en tonos nude...">{{ old('client_notes') }}</textarea>
                </div>
            </div>
        </section>

        {{-- Resumen fijo abajo --}}
        <div class="fixed inset-x-0 bottom-0 z-10 border-t border-nude-200 bg-white/95 px-4 py-3 backdrop-blur">
            <div class="mx-auto flex max-w-xl items-center justify-between gap-4">
                <div class="min-w-0 text-sm">
                    <p id="summary-title" class="truncate font-semibold text-stone-900">Elegí un servicio</p>
                    <p id="summary-detail" class="truncate text-stone-500">&nbsp;</p>
                </div>
                <button type="submit" id="submit-button" class="btn btn-primary shrink-0" disabled>Confirmar turno</button>
            </div>
        </div>
    </form>

    <x-slot:scripts>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('booking-form');
                const slotsContainer = document.getElementById('slots');
                const slotsMessage = document.getElementById('slots-message');
                const liveNotice = document.getElementById('slots-live-notice');
                const submitButton = document.getElementById('submit-button');
                const availabilityUrl = @json(route('availability'));
                let selectedTime = @json(old('time'));
                let requestId = 0;

                const money = (value) => '$ ' + Math.round(value).toLocaleString('es-AR');
                const duration = (minutes) => {
                    const h = Math.floor(minutes / 60), m = minutes % 60;
                    return h === 0 ? `${m}m` : (m === 0 ? `${h}h` : `${h}h ${m}m`);
                };

                const selectedService = () => form.querySelector('input[name="service_id"]:checked');
                const selectedDate = () => form.querySelector('input[name="date"]:checked')?.value;
                const selectedAddons = () => [...form.querySelectorAll('input[name="addons[]"]:checked:not(:disabled)')];

                function showAddonsForService() {
                    const serviceId = selectedService()?.value;

                    form.querySelectorAll('[data-addons-for]').forEach((fieldset) => {
                        const isCurrent = fieldset.dataset.addonsFor === serviceId;
                        fieldset.classList.toggle('hidden', ! isCurrent);
                        fieldset.querySelectorAll('input').forEach((input) => {
                            input.disabled = ! isCurrent;
                        });
                    });
                }

                function updateSummary() {
                    const service = selectedService();
                    const addons = selectedAddons();

                    if (! service) {
                        return;
                    }

                    const total = addons.reduce((sum, addon) => sum + Number(addon.dataset.price), Number(service.dataset.price));
                    const minutes = addons.reduce((sum, addon) => sum + Number(addon.dataset.duration), Number(service.dataset.duration));
                    const when = selectedDate() && selectedTime
                        ? ' · ' + new Date(selectedDate() + 'T00:00').toLocaleDateString('es-AR', { weekday: 'short', day: 'numeric', month: 'short' }) + ' ' + selectedTime
                        : '';

                    document.getElementById('summary-title').textContent = service.dataset.name + (addons.length ? ` + ${addons.length}` : '');
                    document.getElementById('summary-detail').textContent = `${money(total)} · ${duration(minutes)}${when}`;

                    const isComplete = selectedDate() && selectedTime
                        && document.getElementById('name').value.trim()
                        && document.getElementById('phone').value.trim();
                    submitButton.disabled = ! isComplete;
                }

                function renderSlots(slots) {
                    slotsContainer.innerHTML = '';

                    if (slots.length === 0) {
                        slotsMessage.textContent = 'No quedan horarios libres ese día. Probá con otra fecha.';
                        slotsMessage.classList.remove('hidden');
                        selectedTime = null;
                        return;
                    }

                    slotsMessage.classList.add('hidden');

                    slots.forEach((time) => {
                        const label = document.createElement('label');
                        label.className = 'cursor-pointer rounded-xl border border-nude-200 py-2.5 text-center font-semibold transition hover:border-pink-300 has-checked:border-pink-600 has-checked:bg-pink-600 has-checked:text-white';
                        label.innerHTML = `<input type="radio" name="time" value="${time}" class="sr-only">${time}`;
                        label.querySelector('input').checked = time === selectedTime;
                        slotsContainer.append(label);
                    });

                    if (selectedTime && ! slots.includes(selectedTime)) {
                        liveNotice.textContent = `El horario de las ${selectedTime} se acaba de ocupar. Elegí otro, por favor.`;
                        liveNotice.classList.remove('hidden');
                        selectedTime = null;
                    }
                }

                async function loadSlots() {
                    const service = selectedService();
                    const date = selectedDate();

                    if (! service || ! date) {
                        return;
                    }

                    const currentRequest = ++requestId;
                    const params = new URLSearchParams({ service_id: service.value, date });
                    selectedAddons().forEach((addon) => params.append('addons[]', addon.value));

                    slotsMessage.textContent = 'Buscando horarios disponibles…';
                    slotsMessage.classList.remove('hidden');
                    slotsContainer.setAttribute('aria-busy', 'true');

                    try {
                        const response = await fetch(`${availabilityUrl}?${params}`, { headers: { Accept: 'application/json' } });

                        if (! response.ok) {
                            throw new Error(response.statusText);
                        }

                        const data = await response.json();

                        if (currentRequest === requestId) {
                            renderSlots(data.slots);
                        }
                    } catch {
                        slotsMessage.textContent = 'No pudimos cargar los horarios. Revisá tu conexión e intentá de nuevo.';
                    } finally {
                        slotsContainer.removeAttribute('aria-busy');
                        updateSummary();
                    }
                }

                form.addEventListener('change', (event) => {
                    const field = event.target.name;

                    if (field === 'time') {
                        selectedTime = event.target.value;
                        liveNotice.classList.add('hidden');
                    }

                    if (field === 'service_id') {
                        showAddonsForService();
                    }

                    if (['service_id', 'addons[]', 'date'].includes(field)) {
                        loadSlots();
                    }

                    updateSummary();
                });

                form.addEventListener('input', updateSummary);

                /*
                 * Real time: when anyone books, cancels or the salon blocks a
                 * time, the server broadcasts the affected dates through Reverb
                 * and we reload the slots if the client is looking at that day.
                 */
                window.Echo?.channel('agenda').listen('.AgendaUpdated', (event) => {
                    if (event.dates.includes(selectedDate())) {
                        loadSlots();
                    }
                });

                showAddonsForService();
                updateSummary();
                loadSlots();
            });
        </script>
    </x-slot:scripts>
</x-layouts.public>
