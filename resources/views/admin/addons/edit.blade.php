<x-layouts.admin title="Editar {{ $addon->name }}">
    <form method="POST" action="{{ route('admin.addons.update', $addon) }}" class="card max-w-3xl">
        @csrf
        @method('PUT')
        @include('admin.addons._form')

        <div class="mt-6 flex gap-3">
            <button class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</x-layouts.admin>
