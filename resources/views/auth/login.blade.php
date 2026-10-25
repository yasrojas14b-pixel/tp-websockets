<x-layouts.public title="Ingresar">
    <form method="POST" action="{{ route('login') }}" class="card mx-auto flex max-w-sm flex-col gap-4">
        @csrf
        <h1 class="font-display text-2xl font-bold">Panel de gestión</h1>

        <x-flash />

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" class="input" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>
        <div>
            <label for="password" class="label">Contraseña</label>
            <input id="password" name="password" type="password" class="input" required autocomplete="current-password">
        </div>
        <label class="flex items-center gap-2 text-sm text-stone-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-nude-300 text-pink-600"> Recordarme
        </label>
        <button class="btn btn-primary">Ingresar</button>
    </form>
</x-layouts.public>
