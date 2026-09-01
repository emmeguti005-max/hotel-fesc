@extends('layouts.app')

@section('title', 'Comprobante de servicio #'.$solicitud->id)

@php
    $estadoBadge = [
        'resuelto' => 'bg-green-100 text-green-800',
        'no_resuelto' => 'bg-red-100 text-red-800',
    ];
@endphp

@section('content')
    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">solicitud-{{ $solicitud->id }}.comprobante</span>
        </div>
        <div class="p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight">Comprobante de servicio</h2>
                    <p class="text-sm text-stone-500 mt-1">Solicitud #{{ $solicitud->id }} · {{ ucfirst($solicitud->tipo) }}</p>
                </div>
                <span class="badge-retro px-3 py-1 text-xs {{ $estadoBadge[$solicitud->estado] }}">
                    {{ $solicitud->estado === 'resuelto' ? 'Resuelto' : 'No resuelto' }}
                </span>
            </div>

            <dl class="grid grid-cols-2 gap-4 text-sm mb-4 border-t border-b border-stone-100 py-4">
                @if ($solicitud->equipo)
                    <div class="col-span-2">
                        <dt class="text-stone-400">Equipo</dt>
                        <dd class="font-medium">
                            <a href="{{ route('equipos.ficha', $solicitud->equipo) }}" class="text-black underline decoration-black/40 hover:decoration-black underline-offset-2">
                                {{ $solicitud->equipo->nombre }}
                            </a>
                        </dd>
                    </div>
                @endif
                <div class="col-span-2">
                    <dt class="text-stone-400">Ubicación</dt>
                    <dd class="font-medium">
                        Torre {{ $solicitud->ubicacion->area->piso->torre->nombre }} ·
                        {{ $solicitud->ubicacion->area->piso->numero == -1 ? 'Sótano' : 'Piso '.$solicitud->ubicacion->area->piso->numero }} ·
                        {{ $solicitud->ubicacion->area->nombre }} · {{ $solicitud->ubicacion->nombre }}
                    </dd>
                </div>
                <div>
                    <dt class="text-stone-400">Reportado</dt>
                    <dd class="font-medium">{{ $solicitud->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400">Cerrado</dt>
                    <dd class="font-medium">{{ $solicitud->fecha_cierre?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-stone-400">Técnico</dt>
                    <dd class="font-medium">{{ $solicitud->tecnico->nombre ?? '—' }}</dd>
                </div>
            </dl>

            <div class="mb-2">
                <p class="text-xs text-stone-400 uppercase mb-1">Problema reportado</p>
                <p class="text-sm text-stone-700 whitespace-pre-line">{{ $solicitud->descripcion }}</p>
            </div>

            @if ($notaCierre)
                <div class="mt-4">
                    <p class="text-xs text-stone-400 uppercase mb-1">Nota del técnico</p>
                    <p class="text-sm text-stone-700 whitespace-pre-line">{{ $notaCierre->comentario }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
