@extends('layouts.panel')

@section('title', $equipo->nombre)

@php
    use App\Support\Etiquetas;
    $estadoBadge = [
        'resuelto' => 'bg-green-100 text-green-800',
        'no_resuelto' => 'bg-red-100 text-red-700',
    ];
    $piso = $equipo->piso();
@endphp

@section('content')
    @if ($piso)
        <nav class="no-print flex items-center mb-4">
            <a href="{{ route('plano.show', $piso) }}" class="tab-folder">Plano — {{ $piso->etiqueta() }}</a>
            @if ($equipo->habitacion)
                <a href="{{ route('panel.habitaciones.show', $equipo->habitacion) }}" class="tab-folder">Habitación {{ $equipo->habitacion->numero }}</a>
            @endif
        </nav>
    @endif

    <div class="card overflow-hidden no-print">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">activo-{{ $equipo->id }}.ficha</span>
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
            <p class="text-sm text-stone-500 mb-6">{{ Etiquetas::TIPOS_EQUIPO[$equipo->tipo] ?? $equipo->tipo }} · {{ $equipo->lugar() }}</p>

            <dl class="grid grid-cols-2 gap-4 text-sm mb-2">
                <div>
                    <dt class="text-stone-400">Responsable</dt>
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

            <div class="flex flex-wrap gap-3 mt-4">
                @if (auth()->user()->tieneRol('mantenimiento'))
                    <a href="{{ route('panel.equipos.edit', $equipo) }}" class="text-sm text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium">Editar activo</a>
                @endif
                <a href="{{ route('solicitudes.create', $equipo->habitacion_id ? ['habitacion_id' => $equipo->habitacion_id] : []) }}" class="text-sm text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium">Reportar daño</a>
            </div>
        </div>
    </div>

    <div id="qr-card" class="card overflow-hidden mt-6">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">activo-{{ $equipo->id }}.qr</span>
        </div>
        <div class="p-6 flex items-center gap-6">
            <div class="w-32 h-32 shrink-0">{!! \App\Support\Qr::svg(route('equipos.ficha', $equipo)) !!}</div>
            <div>
                <p class="font-bold text-sm">{{ $equipo->nombre }}</p>
                <p class="text-xs text-stone-500">{{ $equipo->codigo ?? 'Sin código' }} · {{ config('hotel.nombre') }}</p>
                <p class="no-print text-xs text-stone-400 mt-2">Pégalo en el activo. Al escanearlo, el personal llega a esta hoja de vida (pide iniciar sesión).</p>
                <button onclick="window.print()" class="no-print mt-3 text-sm text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium">Imprimir sticker</button>
            </div>
        </div>
    </div>

    <style media="print">
        .card:not(#qr-card), h3 { display: none !important; }
        #qr-card { border: none !important; }
    </style>

    <h3 class="text-lg font-extrabold tracking-tight mt-8 mb-4 no-print">Hoja de vida — historial de mantenimientos</h3>

    @forelse ($mantenimientos as $solicitud)
        <div class="card overflow-hidden mb-4">
            <div class="file-titlebar">
                <span class="dot"></span><span class="dot"></span>
                <span class="name">mantenimiento-{{ $solicitud->id }}.log</span>
                <span class="ml-auto badge-retro px-2 py-0.5 text-xs {{ $estadoBadge[$solicitud->estado] }}">
                    {{ Etiquetas::ESTADOS_SOLICITUD[$solicitud->estado] }}
                </span>
            </div>
            <div class="p-4">
                <span class="font-medium text-sm">{{ Etiquetas::TIPOS_SOLICITUD[$solicitud->tipo] ?? $solicitud->tipo }}</span>
                <p class="text-sm text-stone-600 mt-1">{{ $solicitud->descripcion }}</p>
                <p class="text-xs text-stone-400 mt-1">{{ $solicitud->fecha_cierre?->format('d/m/Y') }}</p>
            </div>
        </div>
    @empty
        <div class="card p-6">
            <p class="text-sm text-stone-400">Este activo todavía no tiene mantenimientos registrados.</p>
        </div>
    @endforelse
@endsection
