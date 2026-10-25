<x-layouts.admin title="Nuevo adicional">
    <form method="POST" action="{{ route('admin.addons.store') }}" class="card max-w-3xl">
        @csrf
        @include('admin.addons._form')

        <div class="mt-6 flex gap-3">
            <button class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</x-layouts.admin>
