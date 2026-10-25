<x-layouts.admin title="Editar ficha de {{ $client->name }}">
    <form method="POST" action="{{ route('admin.clients.update', $client) }}" class="card max-w-3xl">
        @csrf
        @method('PUT')
        @include('admin.clients._form')

        <div class="mt-6 flex gap-3">
            <button class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</x-layouts.admin>
