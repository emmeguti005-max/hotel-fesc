@extends('layouts.app')

@section('title', 'Solicitud enviada')

@php
    $estadoBadge = [
        'pendiente' => 'bg-amber-100 text-amber-800',
        'en_proceso' => 'bg-blue-100 text-blue-800',
        'resuelto' => 'bg-green-100 text-green-800',
        'no_resuelto' => 'bg-red-100 text-red-800',
    ];
    $estadoMensaje = [
        'pendiente' => 'El área técnica todavía no la toma. La va a revisar pronto.',
        'en_proceso' => 'Un técnico ya la está atendiendo.',
        'resuelto' => 'Ya se resolvió. ¡Gracias por reportarlo!',
        'no_resuelto' => 'Se revisó, pero no se pudo resolver. El técnico dejó una nota.',
    ];
    $estadoIcono = [
        'pendiente' => ['⏳', 'text-amber-500'],
        'en_proceso' => ['🔧', 'text-blue-500'],
        'resuelto' => ['✓', 'text-green-600'],
        'no_resuelto' => ['✕', 'text-red-600'],
    ];
    [$icono, $iconoColor] = $estadoIcono[$solicitud->estado];
@endphp

@section('content')
    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">solicitud-{{ $solicitud->id }}.estado</span>
        </div>
        <div class="p-6 text-center">
            <div class="{{ $iconoColor }} text-4xl mb-3">{{ $icono }}</div>
            <h2 class="text-2xl font-extrabold tracking-tight mb-1">Solicitud #{{ $solicitud->id }}</h2>
            <span class="badge-retro inline-block px-3 py-1 text-xs mb-4 {{ $estadoBadge[$solicitud->estado] }}">
                {{ ucfirst(str_replace('_', ' ', $solicitud->estado)) }}
            </span>
            <p class="text-stone-600 mb-6">{{ $estadoMensaje[$solicitud->estado] }}</p>

            <dl class="grid grid-cols-2 gap-4 text-sm text-left border-t border-stone-100 pt-4 mb-6">
                <div>
                    <dt class="text-stone-400">Tipo</dt>
                    <dd class="font-medium">{{ ucfirst($solicitud->tipo) }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400">Prioridad</dt>
                    <dd class="font-medium">{{ ucfirst($solicitud->prioridad) }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400">Reportada</dt>
                    <dd class="font-medium">{{ $solicitud->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400">{{ $solicitud->fecha_cierre ? 'Cerrada' : 'Técnico' }}</dt>
                    <dd class="font-medium">
                        {{ $solicitud->fecha_cierre?->format('d/m/Y H:i') ?? $solicitud->tecnico?->nombre ?? 'Sin asignar' }}
                    </dd>
                </div>
            </dl>

            <p class="text-xs text-stone-400 mb-4">Guardá este link para volver a consultar el estado más tarde.</p>

            <a href="{{ route('solicitudes.create') }}"
               class="inline-block btn-retro px-6 py-2 rounded-md font-bold">
                Reportar otro problema
            </a>
        </div>
    </div>
@endsection
