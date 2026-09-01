@extends('layouts.app')

@section('title', $equipo->nombre)
@section('container', 'max-w-2xl')

@php
    $estadoBadge = [
        'resuelto' => 'bg-green-100 text-green-800',
        'no_resuelto' => 'bg-red-100 text-red-700',
    ];
    $piso = $equipo->ubicacion->area->piso ?? null;
    $etiquetaPiso = $piso
        ? ($piso->numero == -1 ? 'Sótano — compartido' : 'Torre '.$piso->torre->nombre.', Piso '.$piso->numero)
        : null;
@endphp

@section('content')
    @if ($piso)
        <nav class="flex items-center mb-4">
            <a href="{{ route('plano.show', $piso) }}" class="tab-folder">
                Plano — {{ $etiquetaPiso }}
            </a>
        </nav>
    @endif

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">equipo-{{ $equipo->id }}.ficha</span>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-2xl font-extrabold tracking-tight flex items-center gap-2">
                    @include('equipos._icono', ['tipo' => $equipo->tipo, 'class' => 'w-6 h-6'])
                    {{ $equipo->nombre }}
                </h2>
                <span class="badge-retro px-2.5 py-0.5 text-xs {{ $equipo->estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-stone-200 text-stone-600' }}">
                    {{ $equipo->estado === 'activo' ? 'Activo' : 'De baja' }}
                </span>
            </div>
            <p class="text-sm text-stone-500 mb-6">
                {{ ucfirst($equipo->tipo) }} ·
                {{ $etiquetaPiso }} ·
                {{ $equipo->ubicacion->area->nombre }} · {{ $equipo->ubicacion->nombre }}
            </p>

            <dl class="grid grid-cols-2 gap-4 text-sm mb-2">
                <div>
                    <dt class="text-stone-400">Usuario asignado</dt>
                    <dd>{{ $equipo->usuario_asignado ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400">Código</dt>
                    <dd>{{ $equipo->codigo ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400">Marca / modelo</dt>
                    <dd>{{ trim(($equipo->marca ?? '').' '.($equipo->modelo ?? '')) ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400">Número de serie</dt>
                    <dd>{{ $equipo->serie ?? '—' }}</dd>
                </div>
            </dl>

            @auth
                <a href="{{ route('panel.equipos.edit', $equipo) }}" class="text-sm text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium">Editar equipo</a>
            @endauth
        </div>
    </div>

    @auth
        <div id="qr-card" class="card overflow-hidden mt-6">
            <div class="file-titlebar">
                <span class="dot"></span><span class="dot"></span>
                <span class="name">equipo-{{ $equipo->id }}.qr</span>
            </div>
            <div class="p-6 flex items-center gap-6">
                <div class="w-32 h-32 shrink-0">{!! \App\Support\Qr::svg(route('equipos.ficha', $equipo)) !!}</div>
                <div>
                    <p class="font-bold text-sm">{{ $equipo->nombre }}</p>
                    <p class="text-xs text-stone-500">{{ $equipo->codigo ?? 'Sin código' }}</p>
                    <p id="qr-hint" class="text-xs text-stone-400 mt-2">Pegalo en el equipo. Al escanearlo, cualquiera llega a esta ficha.</p>
                    <button id="qr-print-btn" onclick="window.print()" class="mt-3 text-sm text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium">Imprimir sticker</button>
                </div>
            </div>
        </div>

        <style media="print">
            header, nav, h3 { display: none !important; }
            .card:not(#qr-card) { display: none !important; }
            #qr-hint, #qr-print-btn { display: none !important; }
            #qr-card { border: none !important; box-shadow: none !important; }
        </style>
    @endauth

    <h3 class="text-lg font-extrabold tracking-tight mt-8 mb-4">Hoja de vida — historial de mantenimientos</h3>

    @forelse ($mantenimientos as $solicitud)
        <div class="card overflow-hidden mb-4">
            <div class="file-titlebar">
                <span class="dot"></span><span class="dot"></span>
                <span class="name">mantenimiento-{{ $solicitud->id }}.log</span>
                <span class="ml-auto badge-retro px-2 py-0.5 text-xs {{ $estadoBadge[$solicitud->estado] }}">
                    {{ ucfirst(str_replace('_', ' ', $solicitud->estado)) }}
                </span>
            </div>
            <div class="p-4">
                <span class="font-medium text-sm">{{ ucfirst($solicitud->tipo) }}</span>
                <p class="text-sm text-stone-600 mt-1">{{ $solicitud->descripcion }}</p>
                <p class="text-xs text-stone-400 mt-1">{{ $solicitud->fecha_cierre?->format('d/m/Y') }}</p>
            </div>
        </div>
    @empty
        <div class="card p-6">
            <p class="text-sm text-stone-400">Este equipo todavía no tiene mantenimientos registrados.</p>
        </div>
    @endforelse
@endsection
