@extends('layouts.panel')

@section('title', 'Solicitud #'.$solicitud->id)

@php
    $estadoBadge = [
        'pendiente' => 'bg-amber-100 text-amber-800',
        'en_proceso' => 'bg-blue-100 text-blue-800',
        'resuelto' => 'bg-green-100 text-green-800',
        'no_resuelto' => 'bg-red-100 text-red-800',
    ];
@endphp

@section('content')
    <a href="{{ route('panel.index') }}" class="text-sm text-stone-500 hover:text-stone-700">&larr; Volver</a>

    <div class="card overflow-hidden mt-4">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">solicitud-{{ $solicitud->id }}.ticket</span>
        </div>
        <div class="p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight">Solicitud #{{ $solicitud->id }} · {{ ucfirst($solicitud->tipo) }}</h2>
                    <p class="text-sm text-stone-500 mt-1">
                        Torre {{ $solicitud->ubicacion->area->piso->torre->nombre }} ·
                        {{ $solicitud->ubicacion->area->piso->numero == -1 ? 'Sótano' : 'Piso '.$solicitud->ubicacion->area->piso->numero }} ·
                        {{ $solicitud->ubicacion->area->nombre }} · {{ $solicitud->ubicacion->nombre }}
                        @if ($solicitud->equipo)
                            · Equipo: {{ $solicitud->equipo->nombre }}
                        @endif
                    </p>
                </div>
                <span class="badge-retro px-3 py-1 text-xs {{ $estadoBadge[$solicitud->estado] }}">
                    {{ ucfirst(str_replace('_', ' ', $solicitud->estado)) }}
                </span>
            </div>

            <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm mb-4 border-t border-b border-stone-100 py-4">
                <div>
                    <dt class="text-stone-400">Prioridad</dt>
                    <dd class="font-medium">{{ ucfirst($solicitud->prioridad) }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400">Reportado por</dt>
                    <dd class="font-medium">{{ $solicitud->nombre_solicitante ?: 'No indicado' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400">Fecha</dt>
                    <dd class="font-medium">{{ $solicitud->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400">Técnico asignado</dt>
                    <dd class="font-medium">{{ $solicitud->tecnico->nombre ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400">IP de origen</dt>
                    <dd class="font-medium">{{ $solicitud->ip_origen ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400">Equipo de red</dt>
                    <dd class="font-medium">{{ $hostnameOrigen ?? 'No identificado' }}</dd>
                </div>
            </dl>

            <p class="text-sm text-stone-700 whitespace-pre-line mb-6">{{ $solicitud->descripcion }}</p>

            <div class="flex flex-wrap gap-3">
                @if ($solicitud->estado === 'pendiente')
                    <form method="POST" action="{{ route('panel.tomar', $solicitud) }}">
                        @csrf
                        <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">
                            Tomar solicitud
                        </button>
                    </form>
                @endif

                @if (in_array($solicitud->estado, ['pendiente', 'en_proceso'], true))
                    <form method="POST" action="{{ route('panel.cerrar', $solicitud) }}" x-data="{ open: false }">
                        @csrf
                        <div x-show="!open">
                            <button type="button" @click="open = true" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-bold border-2 border-black shadow-hard-sm hover:shadow-none hover:translate-x-[3px] hover:translate-y-[3px] transition-all">
                                Cerrar solicitud
                            </button>
                        </div>
                        <div x-show="open" x-cloak class="flex flex-wrap items-center gap-2">
                            <select name="estado" required class="rounded-md input-retro text-sm">
                                <option value="resuelto">Resuelto</option>
                                <option value="no_resuelto">No resuelto</option>
                            </select>
                            <input type="text" name="comentario" placeholder="Nota de cierre (opcional)"
                                   class="rounded-md input-retro text-sm flex-1 min-w-[200px]">
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-bold border-2 border-black shadow-hard-sm hover:shadow-none hover:translate-x-[3px] hover:translate-y-[3px] transition-all">
                                Confirmar
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="card overflow-hidden mt-6">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">seguimiento.log</span>
        </div>
        <div class="p-6">
            <div class="space-y-3 mb-6">
                @forelse ($solicitud->seguimientos as $seguimiento)
                    <div class="text-sm border-l-2 border-stone-200 pl-3">
                        <p class="text-stone-700">{{ $seguimiento->comentario }}</p>
                        <p class="text-xs text-stone-400 mt-1">
                            {{ $seguimiento->usuario->nombre }} · {{ $seguimiento->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-stone-400">Sin comentarios todavía.</p>
                @endforelse
            </div>

            @if ($solicitud->estado !== 'resuelto' && $solicitud->estado !== 'no_resuelto')
                <form method="POST" action="{{ route('panel.comentar', $solicitud) }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="comentario" required placeholder="Agregar una nota de avance..."
                           class="flex-1 rounded-md input-retro text-sm">
                    <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">
                        Comentar
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection
