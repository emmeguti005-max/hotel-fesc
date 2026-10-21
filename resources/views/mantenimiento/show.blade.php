@extends('layouts.panel')

@section('title', 'Reporte #'.$solicitud->id)

@php
    $estadoBadge = [
        'pendiente' => 'bg-amber-100 text-amber-800',
        'en_proceso' => 'bg-blue-100 text-blue-800',
        'resuelto' => 'bg-green-100 text-green-800',
        'no_resuelto' => 'bg-red-100 text-red-800',
    ];
@endphp

@section('content')
    <a href="{{ route('panel.mantenimiento.index') }}" class="no-print text-sm text-stone-500 hover:text-stone-700">&larr; Volver</a>

    <div class="card overflow-hidden mt-4">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">reporte-{{ $solicitud->id }}.ticket</span>
        </div>
        <div class="p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight">Reporte #{{ $solicitud->id }} · {{ \App\Support\Etiquetas::TIPOS_SOLICITUD[$solicitud->tipo] ?? $solicitud->tipo }}</h2>
                    <p class="text-sm text-stone-500 mt-1">
                        @if ($solicitud->habitacion)
                            <a href="{{ route('panel.habitaciones.show', $solicitud->habitacion) }}" class="underline decoration-black/40 underline-offset-2">{{ $solicitud->lugar() }}</a>
                        @else
                            {{ $solicitud->lugar() }}
                        @endif
                        @if ($solicitud->equipo)
                            · Activo: <a href="{{ route('equipos.ficha', $solicitud->equipo) }}" class="underline decoration-black/40 underline-offset-2">{{ $solicitud->equipo->nombre }}</a>
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
            </dl>

            <p class="text-sm text-stone-700 whitespace-pre-line mb-6">{{ $solicitud->descripcion }}</p>

            <div class="flex flex-wrap gap-3">
                @if ($solicitud->estado === 'pendiente' && auth()->user()->tieneRol('mantenimiento'))
                    <form method="POST" action="{{ route('panel.mantenimiento.tomar', $solicitud) }}">
                        @csrf
                        <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">
                            Tomar reporte
                        </button>
                    </form>
                @endif

                @if (in_array($solicitud->estado, ['pendiente', 'en_proceso'], true) && auth()->user()->tieneRol('mantenimiento'))
                    <form method="POST" action="{{ route('panel.mantenimiento.cerrar', $solicitud) }}" x-data="{ open: false }">
                        @csrf
                        <div x-show="!open">
                            <button type="button" @click="open = true" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-bold border-2 border-black shadow-hard-sm hover:shadow-none hover:translate-x-[3px] hover:translate-y-[3px] transition-all">
                                Cerrar reporte
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

    <div class="card overflow-hidden mt-6 no-print">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">evidencias.fotos</span>
        </div>
        <div class="p-6">
            @if ($solicitud->evidencias->isEmpty())
                <p class="text-sm text-stone-400 mb-4">Todavía no hay fotos adjuntas.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                    @foreach ($solicitud->evidencias as $evidencia)
                        <div class="relative group">
                            <a href="{{ asset('storage/'.$evidencia->ruta_archivo) }}" target="_blank" rel="noopener">
                                <img src="{{ asset('storage/'.$evidencia->ruta_archivo) }}" alt="Evidencia de la solicitud #{{ $solicitud->id }}"
                                     class="w-full aspect-square object-cover rounded-md border-2 border-black shadow-hard-sm">
                            </a>
                            @if (auth()->user()->tieneRol('mantenimiento'))
                            <form method="POST" action="{{ route('panel.evidencias.destroy', $evidencia) }}"
                                  class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Eliminar foto"
                                        class="w-6 h-6 flex items-center justify-center rounded-full bg-white border-2 border-black text-xs font-bold leading-none">×</button>
                            </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('panel.evidencias.store', $solicitud) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                @csrf
                <input type="file" name="foto" accept="image/*" required class="text-sm">
                <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">Subir foto</button>
            </form>
        </div>
    </div>

    @if (in_array($solicitud->estado, ['resuelto', 'no_resuelto'], true))
        <div id="qr-card" class="card overflow-hidden mt-6">
            <div class="file-titlebar">
                <span class="dot"></span><span class="dot"></span>
                <span class="name">solicitud-{{ $solicitud->id }}.qr</span>
            </div>
            <div class="p-6 flex items-center gap-6">
                <div class="w-32 h-32 shrink-0">{!! \App\Support\Qr::svg(route('solicitudes.comprobante', $solicitud)) !!}</div>
                <div>
                    <p class="font-bold text-sm">Comprobante de servicio #{{ $solicitud->id }}</p>
                    <p class="text-xs text-stone-500">{{ $solicitud->equipo->nombre ?? $solicitud->lugar() }}</p>
                    <p id="qr-hint" class="text-xs text-stone-400 mt-2">Pégalo en el lugar o activo como registro de que se atendió. Al escanearlo, cualquiera ve el comprobante.</p>
                    <button id="qr-print-btn" onclick="window.print()" class="mt-3 text-sm text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium">Imprimir sticker</button>
                </div>
            </div>
        </div>

        <style media="print">
            header, nav, h3, .no-print { display: none !important; }
            .card:not(#qr-card) { display: none !important; }
            #qr-hint, #qr-print-btn { display: none !important; }
            #qr-card { border: none !important; box-shadow: none !important; }
        </style>
    @endif

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
                <form method="POST" action="{{ route('panel.mantenimiento.comentar', $solicitud) }}" class="flex gap-2">
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
