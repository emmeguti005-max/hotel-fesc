<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Soporte Técnico') · Clínica San José</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen text-stone-800">
    <header class="bg-black text-white border-b-4 border-amber-400">
        <div class="@yield('container', 'max-w-2xl') mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <h1 class="text-lg font-bold tracking-tight">Soporte Técnico · Clínica San José</h1>
                <a href="{{ route('plano.index') }}" class="text-sm text-stone-300 hover:text-amber-300">Plano</a>
            </div>
            @auth
                <a href="{{ route('panel.index') }}" class="text-sm text-stone-300 hover:text-amber-300">Panel técnico</a>
            @else
                <a href="{{ route('login') }}" class="text-sm text-stone-300 hover:text-amber-300">Acceso técnico</a>
            @endauth
        </div>
    </header>

    <main class="@yield('container', 'max-w-2xl') mx-auto px-4 py-8">
        @yield('content')
    </main>
</body>
</html>
