<x-layouts.admin title="Bloqueos de agenda">
    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin.blocks.store') }}" class="card flex h-fit flex-col gap-4">
            @csrf
            <h2 class="text-lg font-semibold">Bloquear un horario</h2>
            <p class="text-sm text-stone-500">Para vacaciones, trámites o descansos puntuales. Los descansos de todos los días se configuran en <a href="{{ route('admin.hours.edit') }}" class="text-pink-700 underline">Horarios</a>.</p>
            <div>
                <label for="starts_at" class="label">Desde</label>
                <input id="starts_at" name="starts_at" type="datetime-local" class="input" required value="{{ old('starts_at') }}">
            </div>
            <div>
                <label for="ends_at" class="label">Hasta</label>
                <input id="ends_at" name="ends_at" type="datetime-local" class="input" required value="{{ old('ends_at') }}">
            </div>
            <div>
                <label for="reason" class="label">Motivo <span class="font-normal text-stone-400">(opcional)</span></label>
                <input id="reason" name="reason" class="input" maxlength="255" value="{{ old('reason') }}" placeholder="Ej: Vacaciones, curso de nail art">
            </div>
            <button class="btn btn-primary">Bloquear</button>
        </form>

        <section class="card overflow-x-auto p-0 lg:col-span-2">
            <h2 class="px-5 pt-5 text-lg font-semibold">Próximos bloqueos</h2>
            <table class="table mt-3">
                <thead><tr><th>Desde</th><th>Hasta</th><th>Motivo</th><th></th></tr></thead>
                <tbody>
                    @forelse ($blocks as $block)
                        <tr>
                            <td class="whitespace-nowrap">{{ $block->starts_at->format('d/m/Y H:i') }}</td>
                            <td class="whitespace-nowrap">{{ $block->ends_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $block->reason ?: '—' }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('admin.blocks.destroy', $block) }}" onsubmit="return confirm('¿Quitar este bloqueo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-stone-400 hover:text-red-600">Quitar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-stone-500">No hay bloqueos próximos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
</x-layouts.admin>
