<x-layouts.admin title="Agenda">
    <x-slot:head>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js"></script>
    </x-slot:head>

    <x-slot:actions>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.blocks.index') }}" class="btn btn-secondary">Bloquear horario</a>
            <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">+ Nuevo turno</a>
        </div>
    </x-slot:actions>

    <div class="mb-4 flex flex-wrap gap-4 text-xs text-stone-600">
        @foreach (\App\Enums\AppointmentStatus::cases() as $status)
            <span class="inline-flex items-center gap-1.5"><span class="size-3 rounded-full" style="background: {{ $status->color() }}"></span>{{ $status->label() }}</span>
        @endforeach
        <span class="inline-flex items-center gap-1.5"><span class="size-3 rounded-full bg-gray-500"></span>Bloqueado</span>
        <span class="text-stone-400">· Arrastrá un turno para moverlo · Hacé clic en un hueco para crear uno</span>
    </div>

    <div id="calendar-error" class="mb-4 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"></div>

    <div class="card p-3 sm:p-5">
        <div id="calendar"></div>
    </div>

    <x-slot:scripts>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const errorBox = document.getElementById('calendar-error');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const createUrl = @json(route('admin.appointments.create'));
                const isMobile = window.matchMedia('(max-width: 640px)').matches;

                function showError(message) {
                    errorBox.textContent = message;
                    errorBox.classList.remove('hidden');
                    setTimeout(() => errorBox.classList.add('hidden'), 6000);
                }

                async function saveNewTime(info) {
                    const response = await fetch(info.event.extendedProps.updateUrl, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ start: info.event.start.toISOString(), end: info.event.end.toISOString() }),
                    });

                    if (! response.ok) {
                        const data = await response.json().catch(() => ({}));
                        info.revert();
                        showError(data.message ?? 'No se pudo mover el turno.');
                    }
                }

                const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                    locale: 'es',
                    timeZone: 'local',
                    initialView: isMobile ? 'timeGridDay' : 'timeGridWeek',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'timeGridDay,timeGridWeek,dayGridMonth,listWeek',
                    },
                    buttonText: { listWeek: 'lista' },
                    height: 'auto',
                    slotMinTime: '08:00:00',
                    slotMaxTime: '21:00:00',
                    slotDuration: '00:15:00',
                    slotLabelInterval: '01:00',
                    allDaySlot: false,
                    nowIndicator: true,
                    businessHours: @json($businessHours),
                    editable: true,
                    eventDurationEditable: true,
                    selectable: true,
                    selectMirror: true,
                    selectAllow: (info) => ! info.allDay,
                    events: @json(route('admin.calendar.events')),
                    eventDrop: saveNewTime,
                    eventResize: saveNewTime,
                    eventClick(info) {
                        if (info.event.extendedProps.editUrl) {
                            info.jsEvent.preventDefault();
                            window.location = info.event.extendedProps.editUrl;
                        }
                    },
                    select(info) {
                        window.location = `${createUrl}?start=${encodeURIComponent(info.start.toISOString())}`;
                    },
                    dateClick(info) {
                        if (info.view.type === 'dayGridMonth') {
                            calendar.changeView('timeGridDay', info.date);
                        }
                    },
                });

                calendar.render();

                // Online bookings appear without reloading the page.
                window.Echo?.channel('agenda').listen('.AgendaUpdated', () => calendar.refetchEvents());
            });
        </script>
    </x-slot:scripts>
</x-layouts.admin>
