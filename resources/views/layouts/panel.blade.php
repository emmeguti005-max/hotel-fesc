<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel técnico') · Clínica San José</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen text-stone-800">
    <header class="bg-black text-white border-b-4 border-amber-400">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('panel.index') }}" class="text-lg font-bold tracking-tight">Clínica San José</a>
                <nav class="flex items-center gap-4 text-sm">
                    <a href="{{ route('panel.dashboard') }}" class="hover:text-amber-300 {{ request()->routeIs('panel.dashboard') ? 'text-amber-300' : 'text-stone-300' }}">Dashboard</a>
                    <a href="{{ route('panel.index') }}" class="hover:text-amber-300 {{ request()->routeIs('panel.index', 'panel.show') ? 'text-amber-300' : 'text-stone-300' }}">Solicitudes</a>
                    <a href="{{ route('panel.equipos.index') }}" class="hover:text-amber-300 {{ request()->routeIs('panel.equipos.*') ? 'text-amber-300' : 'text-stone-300' }}">Equipos</a>
                    <a href="{{ route('plano.index') }}" class="hover:text-amber-300 {{ request()->routeIs('plano.*') ? 'text-amber-300' : 'text-stone-300' }}">Plano</a>
                    @if (auth()->user()->isAdmin())
                        <span class="w-px h-4 bg-stone-700"></span>
                        <span class="text-[10px] uppercase tracking-wider text-stone-500 font-semibold">Admin</span>
                        <a href="{{ route('panel.estructura.torres') }}" class="hover:text-amber-300 {{ request()->routeIs('panel.estructura.*') ? 'text-amber-300' : 'text-stone-300' }}">Estructura</a>
                        <a href="{{ route('panel.auditoria') }}" class="hover:text-amber-300 {{ request()->routeIs('panel.auditoria') ? 'text-amber-300' : 'text-stone-300' }}">Auditoría</a>
                    @endif
                </nav>
            </div>
            <div class="flex items-center gap-4 text-sm text-stone-300">
                <span>{{ auth()->user()->nombre }} <span class="text-stone-500">({{ auth()->user()->rol }})</span></span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="hover:text-amber-300">Salir</button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-100 border-2 border-black text-green-900 text-sm p-3 font-medium shadow-hard-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-100 border-2 border-black text-red-900 text-sm p-3 font-medium shadow-hard-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
