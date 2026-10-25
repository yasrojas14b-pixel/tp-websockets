<x-layouts.admin title="Editar {{ $service->name }}">
    <form method="POST" action="{{ route('admin.services.update', $service) }}" class="card max-w-3xl">
        @csrf
        @method('PUT')
        @include('admin.services._form')

        <p class="mt-4 text-xs text-stone-500">Cambiar precio o duración no modifica los turnos ya reservados.</p>

        <div class="mt-6 flex gap-3">
            <button class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</x-layouts.admin>
