<x-layouts.admin title="Nueva clienta">
    <form method="POST" action="{{ route('admin.clients.store') }}" class="card max-w-3xl">
        @csrf
        @include('admin.clients._form')

        <div class="mt-6 flex gap-3">
            <button class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</x-layouts.admin>
