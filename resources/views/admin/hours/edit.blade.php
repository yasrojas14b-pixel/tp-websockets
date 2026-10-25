<x-layouts.admin title="Horarios de atención">
    <form method="POST" action="{{ route('admin.hours.update') }}" class="card max-w-3xl">
        @csrf
        @method('PUT')

        <p class="mb-5 text-sm text-stone-500">
            Cargá hasta dos franjas por día. El hueco entre la mañana y la tarde funciona como descanso.
            Dejá un día vacío para marcarlo como cerrado.
        </p>

        <div class="flex flex-col divide-y divide-nude-100">
            @foreach ([1, 2, 3, 4, 5, 6, 0] as $weekday)
                @php($ranges = $hoursByWeekday->get($weekday, collect())->values())
                <div class="grid grid-cols-1 items-center gap-3 py-3 sm:grid-cols-[8rem_1fr_1fr]">
                    <p class="font-medium">{{ \App\Models\BusinessHour::WEEKDAY_NAMES[$weekday] }}</p>
                    @foreach ([0 => 'Mañana', 1 => 'Tarde'] as $index => $rangeLabel)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-14 text-stone-500">{{ $rangeLabel }}</span>
                            <input type="time" name="hours[{{ $weekday }}][{{ $index }}][opens_at]" class="input py-1.5" aria-label="{{ $rangeLabel }} apertura"
                                   value="{{ old("hours.$weekday.$index.opens_at", isset($ranges[$index]) ? substr($ranges[$index]->opens_at, 0, 5) : '') }}">
                            <span>a</span>
                            <input type="time" name="hours[{{ $weekday }}][{{ $index }}][closes_at]" class="input py-1.5" aria-label="{{ $rangeLabel }} cierre"
                                   value="{{ old("hours.$weekday.$index.closes_at", isset($ranges[$index]) ? substr($ranges[$index]->closes_at, 0, 5) : '') }}">
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <button class="btn btn-primary mt-6">Guardar horarios</button>
    </form>
</x-layouts.admin>
