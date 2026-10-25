<x-layouts.admin title="Nuevo servicio">
    <form method="POST" action="{{ route('admin.services.store') }}" class="card max-w-3xl">
        @csrf
        @include('admin.services._form')

        <div class="mt-6 flex gap-3">
            <button class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</x-layouts.admin>
