@props(['title'])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#db2777">
    <title>{{ $title }} · {{ config('salon.name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-nude-100 to-nude-50 font-sans text-stone-800 antialiased">
    @if (config('salon.instagram'))
        <a href="{{ config('salon.instagram') }}" target="_blank" rel="noopener"
           class="absolute top-3 left-3 z-20 inline-flex items-center gap-2 rounded-full bg-gradient-to-tr from-amber-400 via-pink-500 to-purple-600 px-3.5 py-2 text-sm font-semibold text-white shadow-md transition hover:scale-105 hover:shadow-lg focus-visible:ring-2 focus-visible:ring-pink-300 focus-visible:outline-none">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <rect x="3" y="3" width="18" height="18" rx="5"/>
                <circle cx="12" cy="12" r="4"/>
                <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
            </svg>
            <span>Ver mis trabajos</span>
        </a>
    @endif

    <header class="px-4 pt-16 pb-4 text-center sm:pt-8">
        <a href="{{ route('booking.create') }}" class="font-display text-4xl font-bold text-pink-700">{{ config('salon.name') }}</a>
        <p class="mt-1 text-sm tracking-widest text-stone-500 uppercase">Nail studio</p>
    </header>

    <main class="mx-auto max-w-xl px-4 pb-28">
        {{ $slot }}
    </main>

    <footer class="pb-8 text-center text-xs text-stone-500">
        {{ config('salon.address') }} ·
        <a href="https://wa.me/{{ config('salon.whatsapp') }}" class="text-pink-700 underline">WhatsApp</a>
        @if (config('salon.instagram'))
            ·
            <a href="{{ config('salon.instagram') }}" target="_blank" rel="noopener" class="text-pink-700 underline">Instagram</a>
        @endif
    </footer>

    {{ $scripts ?? '' }}
</body>
</html>
