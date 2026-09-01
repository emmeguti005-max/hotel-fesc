@extends('layouts.panel')

@section('title', 'Dashboard')

@php
    $estadoLabels = ['pendiente' => 'Pendiente', 'en_proceso' => 'En proceso', 'resuelto' => 'Resuelto', 'no_resuelto' => 'No resuelto'];
    $estadoColor = ['pendiente' => 'bg-amber-400', 'en_proceso' => 'bg-blue-400', 'resuelto' => 'bg-green-500', 'no_resuelto' => 'bg-red-400'];

    $tipoLabels = ['impresora' => 'Impresora', 'instalacion' => 'Instalación eléctrica', 'red' => 'Red / internet', 'hardware' => 'Hardware', 'software' => 'Software', 'otro' => 'Otro'];
    $prioridadLabels = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta', 'urgente' => 'Urgente'];
    $prioridadColor = ['baja' => 'bg-stone-400', 'media' => 'bg-stone-500', 'alta' => 'bg-orange-400', 'urgente' => 'bg-red-500'];

    $maxEstado = max(1, max($porEstado));
    $maxTipo = max(1, max($porTipo));
    $maxPrioridad = max(1, max($porPrioridad));
@endphp

@section('content')
    <h2 class="text-2xl font-extrabold tracking-tight mb-6">Dashboard</h2>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="card p-4">
            <p class="text-xs text-stone-500 uppercase">Total solicitudes</p>
            <p class="text-2xl font-semibold mt-1">{{ $totalSolicitudes }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-stone-500 uppercase">Pendientes</p>
            <p class="text-2xl font-semibold mt-1">{{ $porEstado['pendiente'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-stone-500 uppercase">En proceso</p>
            <p class="text-2xl font-semibold mt-1">{{ $porEstado['en_proceso'] }}</p>
        </div>
        <a href="{{ route('panel.index', ['vencidas' => 1]) }}" class="card p-4 {{ $vencidas > 0 ? 'bg-red-50' : '' }}">
            <p class="text-xs text-stone-500 uppercase">Atrasadas</p>
            <p class="text-2xl font-semibold mt-1 {{ $vencidas > 0 ? 'text-red-700' : '' }}">{{ $vencidas }}</p>
        </a>
        <div class="card p-4">
            <p class="text-xs text-stone-500 uppercase">Tiempo prom. resolución</p>
            <p class="text-2xl font-semibold mt-1">
                {{ $tiempoPromedioHoras !== null ? round($tiempoPromedioHoras, 1).' h' : '—' }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card p-4">
            <h3 class="text-sm font-semibold text-stone-500 uppercase mb-3">Por estado</h3>
            <div class="space-y-2">
                @foreach ($porEstado as $clave => $valor)
                    <div>
                        <div class="flex justify-between text-xs text-stone-600 mb-0.5">
                            <span>{{ $estadoLabels[$clave] }}</span>
                            <span>{{ $valor }}</span>
                        </div>
                        <div class="w-full bg-stone-100 rounded-full h-2 border border-black/10">
                            <div class="{{ $estadoColor[$clave] }} h-2 rounded-full" style="width: {{ round($valor / $maxEstado * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-4">
            <h3 class="text-sm font-semibold text-stone-500 uppercase mb-3">Por tipo de problema</h3>
            <div class="space-y-2">
                @foreach ($porTipo as $clave => $valor)
                    <div>
                        <div class="flex justify-between text-xs text-stone-600 mb-0.5">
                            <span>{{ $tipoLabels[$clave] }}</span>
                            <span>{{ $valor }}</span>
                        </div>
                        <div class="w-full bg-stone-100 rounded-full h-2 border border-black/10">
                            <div class="bg-black h-2 rounded-full" style="width: {{ round($valor / $maxTipo * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-4">
            <h3 class="text-sm font-semibold text-stone-500 uppercase mb-3">Por prioridad</h3>
            <div class="space-y-2">
                @foreach ($porPrioridad as $clave => $valor)
                    <div>
                        <div class="flex justify-between text-xs text-stone-600 mb-0.5">
                            <span>{{ $prioridadLabels[$clave] }}</span>
                            <span>{{ $valor }}</span>
                        </div>
                        <div class="w-full bg-stone-100 rounded-full h-2 border border-black/10">
                            <div class="{{ $prioridadColor[$clave] }} h-2 rounded-full" style="width: {{ round($valor / $maxPrioridad * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if (auth()->user()->isAdmin())
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="card p-4">
                <h3 class="text-sm font-semibold text-stone-500 uppercase mb-3">Solicitudes por torre</h3>
                @if ($porTorre->isEmpty())
                    <p class="text-sm text-stone-400">Sin datos todavía.</p>
                @else
                    @php $maxTorre = max(1, $porTorre->max('total')); @endphp
                    <div class="space-y-2">
                        @foreach ($porTorre as $fila)
                            <div>
                                <div class="flex justify-between text-xs text-stone-600 mb-0.5">
                                    <span>Torre {{ $fila->nombre }}</span>
                                    <span>{{ $fila->total }}</span>
                                </div>
                                <div class="w-full bg-stone-100 rounded-full h-2 border border-black/10">
                                    <div class="bg-black h-2 rounded-full" style="width: {{ round($fila->total / $maxTorre * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card p-4">
                <h3 class="text-sm font-semibold text-stone-500 uppercase mb-3">Equipos con más solicitudes</h3>
                @if ($topEquipos->isEmpty())
                    <p class="text-sm text-stone-400">Sin datos todavía.</p>
                @else
                    <ul class="divide-y divide-stone-100 text-sm">
                        @foreach ($topEquipos as $equipo)
                            <li class="py-2 flex items-center justify-between">
                                <a href="{{ route('equipos.ficha', $equipo) }}" class="text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium">{{ $equipo->nombre }}</a>
                                <span class="text-stone-500">{{ $equipo->solicitudes_count }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card p-4">
                <h3 class="text-sm font-semibold text-stone-500 uppercase mb-3">Ranking de técnicos (resueltas)</h3>
                @if ($rankingTecnicos->isEmpty())
                    <p class="text-sm text-stone-400">Sin técnicos registrados.</p>
                @else
                    <ul class="divide-y divide-stone-100 text-sm">
                        @foreach ($rankingTecnicos as $tecnico)
                            <li class="py-2 flex items-center justify-between">
                                <span>{{ $tecnico->nombre }}</span>
                                <span class="text-stone-500">{{ $tecnico->resueltas_count }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif
@endsection
