<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientPhotoController extends Controller
{
    public function store(Request $request, Client $client): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
        ], attributes: ['photo' => 'foto']);

        $client->photos()->create([
            'path' => $request->file('photo')->store("clients/{$client->id}", 'public'),
            'caption' => $request->input('caption'),
        ]);

        return back()->with('status', 'Foto guardada.');
    }

    public function destroy(Client $client, ClientPhoto $photo): RedirectResponse
    {
        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return back()->with('status', 'Foto eliminada.');
    }
}
