@props(['title'])

@php
    $navigation = [
        ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Inicio'],
        ['route' => 'admin.calendar', 'match' => 'admin.calendar*', 'label' => 'Agenda'],
        ['route' => 'admin.appointments.index', 'match' => 'admin.appointments.*', 'label' => 'Turnos'],
        ['route' => 'admin.clients.index', 'match' => 'admin.clients.*', 'label' => 'Clientas'],
        ['route' => 'admin.services.index', 'match' => ['admin.services.*', 'admin.addons.*'], 'label' => 'Servicios'],
        ['route' => 'admin.blocks.index', 'match' => 'admin.blocks.*', 'label' => 'Bloqueos'],
        ['route' => 'admin.hours.edit', 'match' => 'admin.hours.*', 'label' => 'Horarios'],
    ];
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · {{ config('salon.name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body class="min-h-screen bg-nude-50 font-sans text-stone-800 antialiased">
    <div class="lg:flex">
        <aside class="border-b border-nude-200 bg-white lg:sticky lg:top-0 lg:h-screen lg:w-60 lg:shrink-0 lg:border-r lg:border-b-0">
            <div class="flex items-center justify-between px-5 py-4 lg:block">
                <a href="{{ route('admin.dashboard') }}" class="font-display text-2xl font-bold text-pink-700">{{ config('salon.name') }}</a>
                <p class="hidden text-xs text-stone-500 lg:block">Panel de gestión</p>
                <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                    @csrf
                    <button class="text-sm text-stone-500 hover:text-pink-700">Salir</button>
                </form>
            </div>
            <nav class="flex gap-1 overflow-x-auto px-3 pb-3 lg:flex-col lg:px-3">
                @foreach ($navigation as $item)
                    <a href="{{ route($item['route']) }}"
                       @class([
                           'whitespace-nowrap rounded-xl px-3 py-2 text-sm font-medium transition',
                           'bg-pink-50 text-pink-700' => request()->routeIs($item['match']),
                           'text-stone-600 hover:bg-nude-50' => ! request()->routeIs($item['match']),
                       ])>{{ $item['label'] }}</a>
                @endforeach
            </nav>
            <div class="hidden px-5 py-4 lg:absolute lg:bottom-0 lg:block lg:w-60">
                <a href="{{ route('booking.create') }}" target="_blank" class="block text-sm text-stone-500 hover:text-pink-700">Ver página de reservas ↗</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button class="text-sm text-stone-500 hover:text-pink-700">Cerrar sesión ({{ auth()->user()->name }})</button>
                </form>
            </div>
        </aside>

        <main class="min-w-0 flex-1 px-4 py-6 sm:px-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 class="font-display text-3xl font-bold text-stone-900">{{ $title }}</h1>
                {{ $actions ?? '' }}
            </div>

            <x-flash />

            {{ $slot }}
        </main>
    </div>

    {{ $scripts ?? '' }}
</body>
</html>
