<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q'));
        $phoneDigits = Client::normalizePhone($search);

        $clients = Client::query()
            ->withCount('appointments')
            ->withMax('appointments', 'starts_at')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search, $phoneDigits): void {
                $query->where('name', 'like', "%{$search}%");

                if ($phoneDigits !== '') {
                    $query->orWhere('phone', 'like', "%{$phoneDigits}%");
                }
            }))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.clients.index', ['clients' => $clients, 'search' => $search]);
    }

    public function create(): View
    {
        return view('admin.clients.create', ['client' => new Client]);
    }

    public function store(Request $request): RedirectResponse
    {
        $client = Client::create($this->validated($request));

        return redirect()->route('admin.clients.show', $client)->with('status', 'Clienta registrada.');
    }

    public function show(Client $client): View
    {
        $client->load([
            'photos' => fn ($query) => $query->latest(),
            'appointments' => fn ($query) => $query->with(['service', 'addons'])->orderByDesc('starts_at'),
        ]);

        return view('admin.clients.show', ['client' => $client]);
    }

    public function edit(Client $client): View
    {
        return view('admin.clients.edit', ['client' => $client]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $client->update($this->validated($request, $client));

        return redirect()->route('admin.clients.show', $client)->with('status', 'Ficha actualizada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Client $client = null): array
    {
        $request->merge(['phone' => Client::normalizePhone((string) $request->input('phone'))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'min:8', 'max:30', Rule::unique('clients', 'phone')->ignore($client)],
            'email' => ['nullable', 'email', 'max:255'],
            'nail_type' => ['nullable', 'string', 'max:255'],
            'allergies' => ['nullable', 'string', 'max:1000'],
            'preferred_designs' => ['nullable', 'string', 'max:1000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ], attributes: ['name' => 'nombre', 'phone' => 'teléfono']);
    }
}
