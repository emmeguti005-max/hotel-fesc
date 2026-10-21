@extends('layouts.panel')

@section('title', 'Habitación '.$habitacion->numero)

@php
    use App\Support\Dinero;
    use App\Support\Etiquetas;
    $u = auth()->user();
    $reserva = $habitacion->reservaActual;
@endphp

@section('content')
    <a href="{{ route('panel.habitaciones.index') }}" class="no-print text-sm text-stone-500 hover:text-stone-700">&larr; Tablero</a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-4">
        <div class="lg:col-span-2 space-y-6 no-print">
            <div class="card overflow-hidden">
                <div class="file-titlebar">
                    <span class="dot"></span><span class="dot"></span>
                    <span class="name">habitacion-{{ $habitacion->numero }}.ficha</span>
                    <span class="ml-auto badge-retro px-2 py-0.5 text-xs {{ Etiquetas::COLOR_HABITACION[$habitacion->estado] }}">{{ Etiquetas::ESTADOS_HABITACION[$habitacion->estado] }}</span>
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-stone-500 font-semibold">{{ $habitacion->piso->etiqueta() }}</p>
                            <h2 class="text-3xl font-extrabold tracking-tight">{{ $habitacion->numero }}</h2>
                            <p class="text-sm text-stone-600">{{ Etiquetas::TIPOS_HABITACION[$habitacion->tipo] }} · {{ $habitacion->capacidad }} personas · {{ Dinero::cop($habitacion->tarifa_noche) }} / noche</p>
                            @if ($habitacion->descripcion)
                                <p class="text-sm text-stone-500 mt-1">{{ $habitacion->descripcion }}</p>
                            @endif
                        </div>
                        @if ($u->isAdmin())
                            <a href="{{ route('panel.habitaciones.edit', $habitacion) }}" class="chip-retro">Editar</a>
                        @endif
                    </div>

                    @if ($reserva)
                        <div class="mt-5 border-2 border-black rounded-md p-4 bg-blue-50">
                            <p class="text-xs uppercase font-semibold text-stone-500">Hospedado</p>
                            <p class="font-bold">{{ $reserva->huesped->nombre }}</p>
                            <p class="text-sm text-stone-600">{{ $reserva->fecha_entrada->format('d/m') }} → {{ $reserva->fecha_salida->format('d/m/Y') }} · {{ $reserva->adultos + $reserva->ninos }} persona(s)</p>
                            <div class="flex flex-wrap gap-2 mt-3">
                                @if ($u->tieneRol('recepcion'))
                                    <a href="{{ route('panel.reservas.show', $reserva) }}" class="chip-retro">Ver estadía / check-out</a>
                                @endif
                                @if ($u->tieneRol('restaurante', 'recepcion'))
                                    <a href="{{ route('panel.pedidos.create', ['habitacion_id' => $habitacion->id]) }}" class="chip-retro">+ Pedido del restaurante</a>
                                @endif
                            </div>
                            @if ($consumos->isNotEmpty())
                                <p class="text-xs text-stone-600 mt-3">Consumos de esta estadía: {{ $consumos->count() }} pedido(s) · {{ Dinero::cop($consumos->where('estado', '!=', 'cancelado')->sum('total')) }}</p>
                            @endif
                        </div>
                    @elseif ($u->tieneRol('recepcion') && $habitacion->estado === 'disponible')
                        <a href="{{ route('panel.reservas.create', ['habitacion_id' => $habitacion->id]) }}" class="btn-retro px-4 py-2 rounded-md text-sm font-bold mt-5">Reservar / registrar llegada</a>
                    @endif

                    @if ($u->tieneRol('recepcion', 'mantenimiento') && $habitacion->estado !== 'ocupada')
                        <form method="POST" action="{{ route('panel.habitaciones.estado', $habitacion) }}" class="mt-5 flex flex-wrap items-center gap-2">
                            @csrf
                            <span class="text-sm font-medium">Cambiar estado:</span>
                            <select name="estado" class="rounded-md input-retro text-sm">
                                @foreach (['sucia' => 'Marcar sucia (crea tarea de aseo)', 'mantenimiento' => 'En mantenimiento', 'bloqueada' => 'Bloqueada', 'disponible' => 'Disponible (tras mantenimiento/bloqueo)'] as $valor => $texto)
                                    <option value="{{ $valor }}">{{ $texto }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-retro px-3 py-1.5 rounded-md text-sm font-bold">Aplicar</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="card overflow-hidden">
                    <div class="file-titlebar">
                        <span class="dot"></span><span class="dot"></span>
                        <span class="name">aseo.log</span>
                    </div>
                    <ul class="divide-y divide-stone-100 text-sm">
                        @forelse ($tareas as $tarea)
                            <li class="px-4 py-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-medium">{{ Etiquetas::TIPOS_ASEO[$tarea->tipo] }}</span>
                                    <span class="badge-retro px-2 py-0.5 text-xs {{ Etiquetas::COLOR_ASEO[$tarea->estado] }}">{{ Etiquetas::ESTADOS_ASEO[$tarea->estado] }}</span>
                                </div>
                                <p class="text-xs text-stone-500">
                                    {{ $tarea->asignadaA->nombre ?? 'Sin asignar' }}
                                    @if ($tarea->fin_at)
                                        · {{ $tarea->fin_at->format('d/m H:i') }} · {{ $tarea->minutos() }} min
                                    @else
                                        · creada {{ $tarea->created_at->diffForHumans() }}
                                    @endif
                                </p>
                            </li>
                        @empty
                            <li class="px-4 py-3 text-stone-400">Sin registros de aseo.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="card overflow-hidden">
                    <div class="file-titlebar">
                        <span class="dot"></span><span class="dot"></span>
                        <span class="name">mantenimiento.log</span>
                    </div>
                    <ul class="divide-y divide-stone-100 text-sm">
                        @forelse ($solicitudes as $solicitud)
                            <li class="px-4 py-2 flex items-center justify-between gap-2">
                                <span class="truncate">
                                    @if ($u->tieneRol('mantenimiento', 'recepcion'))
                                        <a href="{{ route('panel.mantenimiento.show', $solicitud) }}" class="underline decoration-black/30 underline-offset-2">#{{ $solicitud->id }}</a>
                                    @else
                                        #{{ $solicitud->id }}
                                    @endif
                                    {{ Etiquetas::TIPOS_SOLICITUD[$solicitud->tipo] }}
                                </span>
                                <span class="badge-retro px-2 py-0.5 text-xs {{ Etiquetas::COLOR_SOLICITUD[$solicitud->estado] }}">{{ Etiquetas::ESTADOS_SOLICITUD[$solicitud->estado] }}</span>
                            </li>
                        @empty
                            <li class="px-4 py-3 text-stone-400">Sin reportes de daños.</li>
                        @endforelse
                    </ul>
                    <div class="px-4 py-2 border-t border-stone-100">
                        <a href="{{ route('solicitudes.create', ['habitacion_id' => $habitacion->id]) }}" class="text-xs underline decoration-black/40 underline-offset-2 font-medium">+ Reportar daño</a>
                    </div>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="file-titlebar">
                    <span class="dot"></span><span class="dot"></span>
                    <span class="name">dotacion.inventario</span>
                </div>
                <div class="p-4 flex flex-wrap gap-2">
                    @forelse ($habitacion->equipos as $equipo)
                        <a href="{{ route('equipos.ficha', $equipo) }}" class="chip-retro {{ $equipo->estado === 'de_baja' ? 'opacity-50 line-through' : '' }}">
                            @include('equipos._icono', ['tipo' => $equipo->tipo, 'class' => 'w-4 h-4'])
                            {{ $equipo->nombre }}
                        </a>
                    @empty
                        <p class="text-sm text-stone-400">Sin activos registrados en esta habitación.</p>
                    @endforelse
                </div>
            </div>

            @if ($u->tieneRol('recepcion'))
                <div class="card overflow-hidden">
                    <div class="file-titlebar">
                        <span class="dot"></span><span class="dot"></span>
                        <span class="name">historial-estadias.log</span>
                    </div>
                    <ul class="divide-y divide-stone-100 text-sm">
                        @forelse ($reservas as $r)
                            <li class="px-4 py-2 flex items-center justify-between gap-2">
                                <a href="{{ route('panel.reservas.show', $r) }}" class="underline decoration-black/30 underline-offset-2">{{ $r->codigo }} · {{ $r->huesped->nombre }}</a>
                                <span class="text-xs text-stone-500">{{ $r->fecha_entrada->format('d/m') }} → {{ $r->fecha_salida->format('d/m/Y') }}
                                    <span class="badge-retro px-2 py-0.5 ml-1 {{ Etiquetas::COLOR_RESERVA[$r->estado] }}">{{ Etiquetas::ESTADOS_RESERVA[$r->estado] }}</span>
                                </span>
                            </li>
                        @empty
                            <li class="px-4 py-3 text-stone-400">Sin reservas todavía.</li>
                        @endforelse
                    </ul>
                </div>
            @endif
        </div>

        {{-- QR de la habitación: se imprime y se pega en la puerta o en la mesa de noche --}}
        <div id="qr-card" class="card overflow-hidden self-start">
            <div class="file-titlebar">
                <span class="dot"></span><span class="dot"></span>
                <span class="name">habitacion-{{ $habitacion->numero }}.qr</span>
            </div>
            <div class="p-6 text-center">
                <p class="text-xs uppercase tracking-wider font-semibold text-stone-500">{{ config('hotel.nombre') }}</p>
                <p class="text-2xl font-extrabold">Habitación {{ $habitacion->numero }}</p>
                <div class="w-48 h-48 mx-auto my-3 border-2 border-black rounded-md p-1 bg-white">
                    {!! \App\Support\Qr::svg(route('portal.habitacion', $habitacion)) !!}
                </div>
                <p class="text-sm font-semibold">Escanéame para pedir al restaurante, solicitar aseo o reportar un daño.</p>
                <div class="no-print mt-4 space-y-2">
                    <button onclick="window.print()" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">Imprimir QR</button>
                    <a href="{{ route('portal.habitacion', $habitacion) }}" target="_blank" class="block text-xs underline decoration-black/40 underline-offset-2">Abrir como huésped ↗</a>
                </div>
            </div>
        </div>
    </div>

    <style media="print">
        .card:not(#qr-card) { display: none !important; }
        #qr-card { margin: 0 auto; max-width: 9cm; }
    </style>
@endsection
