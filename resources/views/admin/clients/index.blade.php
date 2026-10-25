<x-layouts.admin title="Clientas">
    <x-slot:actions>
        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">+ Nueva clienta</a>
    </x-slot:actions>

    <form method="GET" class="mb-5 flex max-w-md gap-2">
        <input type="search" name="q" value="{{ $search }}" class="input" placeholder="Buscar por nombre o teléfono…" aria-label="Buscar clienta">
        <button class="btn btn-secondary">Buscar</button>
    </form>

    <div class="card overflow-x-auto p-0">
        <table class="table">
            <thead><tr><th>Nombre</th><th>WhatsApp</th><th>Turnos</th><th>Último turno</th><th></th></tr></thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td><a href="{{ route('admin.clients.show', $client) }}" class="font-medium hover:text-pink-700">{{ $client->name }}</a></td>
                        <td><a href="{{ $client->whatsapp_url }}" target="_blank" rel="noopener" class="text-pink-700 hover:underline">{{ $client->phone }}</a></td>
                        <td>{{ $client->appointments_count }}</td>
                        <td class="text-stone-500">{{ $client->appointments_max_starts_at ? \Illuminate\Support\Carbon::parse($client->appointments_max_starts_at)->format('d/m/Y') : '—' }}</td>
                        <td class="text-right"><a href="{{ route('admin.clients.show', $client) }}" class="text-pink-700 hover:underline">Ver ficha</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-stone-500">No se encontraron clientas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>
</x-layouts.admin>
