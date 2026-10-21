<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel') · {{ config('hotel.nombre') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
@php
    $u = auth()->user();
    $menu = array_filter([
        $u->tieneRol('recepcion') ? ['Tablero', 'panel.dashboard', 'panel.dashboard'] : null,
        ['Habitaciones', 'panel.habitaciones.index', 'panel.habitaciones.*'],
        $u->tieneRol('recepcion') ? ['Reservas', 'panel.reservas.index', ['panel.reservas.*', 'panel.huespedes.*']] : null,
        $u->tieneRol('aseo', 'recepcion') ? ['Aseo', 'panel.aseo.index', 'panel.aseo.*'] : null,
        $u->tieneRol('restaurante', 'recepcion') ? ['Cocina', 'panel.pedidos.index', 'panel.pedidos.*'] : null,
        $u->tieneRol('restaurante') ? ['Menú', 'panel.productos.index', 'panel.productos.*'] : null,
        $u->tieneRol('recepcion') ? ['Facturas', 'panel.facturas.index', 'panel.facturas.*'] : null,
        $u->tieneRol('mantenimiento', 'recepcion') ? ['Mantenimiento', 'panel.mantenimiento.index', 'panel.mantenimiento.*'] : null,
        $u->tieneRol('mantenimiento') ? ['Activos', 'panel.equipos.index', ['panel.equipos.*', 'equipos.*']] : null,
        ['Plano', 'plano.index', 'plano.*'],
    ]);
    $menuAdmin = [
        ['Personal', 'panel.personal.index', 'panel.personal.*'],
        ['Estructura', 'panel.estructura.torres', 'panel.estructura.*'],
        ['Auditoría', 'panel.auditoria', 'panel.auditoria'],
    ];
@endphp
<body class="min-h-screen text-stone-800">
    <header class="bg-black text-white border-b-4 border-amber-400 no-print">
        <div class="max-w-6xl mx-auto px-4 pt-4 pb-3 flex items-center justify-between gap-4">
            <a href="{{ route('panel.inicio') }}" class="flex items-center gap-2 text-lg font-bold tracking-tight">
                <span class="inline-flex w-7 h-7 items-center justify-center rounded bg-amber-400 text-black text-sm font-extrabold">H</span>
                {{ config('hotel.nombre') }}
            </a>
            <div class="flex items-center gap-4 text-sm text-stone-300">
                <span class="hidden sm:inline">{{ $u->nombre }} <span class="text-stone-500">· {{ \App\Support\Etiquetas::ROLES[$u->rol] ?? $u->rol }}</span></span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="hover:text-amber-300">Salir</button>
                </form>
            </div>
        </div>
        <nav class="max-w-6xl mx-auto px-4 pb-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
            @foreach ($menu as [$texto, $ruta, $activo])
                <a href="{{ route($ruta) }}" class="hover:text-amber-300 {{ request()->routeIs(...(array) $activo) ? 'text-amber-300 font-semibold' : 'text-stone-300' }}">{{ $texto }}</a>
            @endforeach
            @if ($u->isAdmin())
                <span class="w-px h-4 bg-stone-700"></span>
                <span class="text-[10px] uppercase tracking-wider text-stone-500 font-semibold">Admin</span>
                @foreach ($menuAdmin as [$texto, $ruta, $activo])
                    <a href="{{ route($ruta) }}" class="hover:text-amber-300 {{ request()->routeIs($activo) ? 'text-amber-300 font-semibold' : 'text-stone-300' }}">{{ $texto }}</a>
                @endforeach
            @endif
        </nav>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        @if (session('status'))
            <div class="no-print mb-4 rounded-md bg-green-100 border-2 border-black text-green-900 text-sm p-3 font-medium shadow-hard-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="no-print mb-4 rounded-md bg-red-100 border-2 border-black text-red-900 text-sm p-3 font-medium shadow-hard-sm">
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
