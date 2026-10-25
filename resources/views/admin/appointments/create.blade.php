<x-layouts.admin title="Nuevo turno">
    <form method="POST" action="{{ route('admin.appointments.store') }}" class="card max-w-3xl">
        @csrf
        @include('admin.appointments._form')

        <div class="mt-6 flex gap-3">
            <button class="btn btn-primary">Guardar turno</button>
            <a href="{{ route('admin.calendar') }}" class="btn btn-secondary">Volver</a>
        </div>
    </form>
</x-layouts.admin>
