<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bienvenido') · {{ config('hotel.nombre') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen text-stone-800">
    <header class="bg-black text-white border-b-4 border-amber-400 no-print">
        <div class="@yield('container', 'max-w-2xl') mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('inicio') }}" class="flex items-center gap-2 text-lg font-bold tracking-tight">
                <span class="inline-flex w-7 h-7 items-center justify-center rounded bg-amber-400 text-black text-sm font-extrabold">H</span>
                {{ config('hotel.nombre') }}
            </a>
            @auth
                <a href="{{ route('panel.inicio') }}" class="text-sm text-stone-300 hover:text-amber-300">Ir al panel</a>
            @else
                <a href="{{ route('login') }}" class="text-sm text-stone-300 hover:text-amber-300">Acceso personal</a>
            @endauth
        </div>
    </header>

    <main class="@yield('container', 'max-w-2xl') mx-auto px-4 py-8">
        @if (session('status'))
            <div class="no-print mb-4 rounded-md bg-green-100 border-2 border-black text-green-900 text-sm p-3 font-medium shadow-hard-sm">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
