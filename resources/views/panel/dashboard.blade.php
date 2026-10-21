@extends('layouts.panel')

@section('title', 'Tablero')

@php
    use App\Support\Dinero;
    use App\Support\Etiquetas;
    $colorBarra = [
        'disponible' => 'bg-green-500',
        'ocupada' => 'bg-blue-500',
        'sucia' => 'bg-amber-400',
        'en_limpieza' => 'bg-cyan-400',
        'mantenimiento' => 'bg-red-500',
        'bloqueada' => 'bg-stone-400',
    ];
    $maxDia = max(1, $ultimos7->max());
@endphp

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight">Tablero del hotel</h2>
            <p class="text-sm text-stone-500">{{ now()->translatedFormat('l j \d\e F, Y') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('panel.reservas.create') }}" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">+ Reserva</a>
            <a href="{{ route('panel.pedidos.create') }}" class="chip-retro">+ Pedido</a>
        </div>
    </div>

    {{-- Indicadores principales --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="card p-4 bg-amber-100">
            <p class="text-xs text-stone-600 uppercase font-semibold">Ocupación</p>
            <p class="text-3xl font-extrabold mt-1">{{ $ocupacion }}%</p>
            <p class="text-xs text-stone-600">{{ $porEstado['ocupada'] }} de {{ $totalHabitaciones }} hab.</p>
        </div>
        <a href="{{ route('panel.reservas.index', ['vista' => 'llegadas']) }}" class="card p-4">
            <p class="text-xs text-stone-500 uppercase font-semibold">Llegadas</p>
            <p class="text-3xl font-extrabold mt-1">{{ $llegadas->count() }}</p>
            <p class="text-xs text-stone-500">pendientes de check-in</p>
        </a>
        <a href="{{ route('panel.reservas.index', ['vista' => 'salidas']) }}" class="card p-4 {{ $salidas->isNotEmpty() ? 'bg-blue-50' : '' }}">
            <p class="text-xs text-stone-500 uppercase font-semibold">Salidas</p>
            <p class="text-3xl font-extrabold mt-1">{{ $salidas->count() }}</p>
            <p class="text-xs text-stone-500">check-out de hoy</p>
        </a>
        <div class="card p-4">
            <p class="text-xs text-stone-500 uppercase font-semibold">Ingresos hoy</p>
            <p class="text-2xl font-extrabold mt-1">{{ Dinero::cop($ingresosHoy) }}</p>
            <p class="text-xs text-stone-500">Mes: {{ Dinero::cop($ingresosMes) }}</p>
        </div>
        <a href="{{ route('panel.aseo.index') }}" class="card p-4 {{ $aseoPendiente ? 'bg-amber-50' : '' }}">
            <p class="text-xs text-stone-500 uppercase font-semibold">Aseo abierto</p>
            <p class="text-3xl font-extrabold mt-1">{{ $aseoPendiente }}</p>
            <p class="text-xs text-stone-500">tareas sin terminar</p>
        </a>
        <a href="{{ route('panel.pedidos.index') }}" class="card p-4">
            <p class="text-xs text-stone-500 uppercase font-semibold">Pedidos en curso</p>
            <p class="text-3xl font-extrabold mt-1">{{ $pedidosActivos }}</p>
            <p class="text-xs text-stone-500">cocina y despacho</p>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="card p-4">
            <h3 class="text-sm font-semibold text-stone-500 uppercase mb-3">Habitaciones por estado</h3>
            <div class="space-y-2">
                @foreach ($porEstado as $estado => $valor)
                    <a href="{{ route('panel.habitaciones.index', ['estado' => $estado]) }}" class="block">
                        <div class="flex justify-between text-xs text-stone-600 mb-0.5">
                            <span>{{ Etiquetas::ESTADOS_HABITACION[$estado] }}</span>
                            <span>{{ $valor }}</span>
                        </div>
                        <div class="w-full bg-stone-100 rounded-full h-2 border border-black/10">
                            <div class="{{ $colorBarra[$estado] }} h-2 rounded-full" style="width: {{ $totalHabitaciones ? round($valor / $totalHabitaciones * 100) : 0 }}%"></div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="card p-4">
            <h3 class="text-sm font-semibold text-stone-500 uppercase mb-3">Facturación últimos 7 días</h3>
            <div class="flex items-end gap-2 h-36">
                @foreach ($ultimos7 as $dia => $valor)
                    <div class="flex-1 flex flex-col items-center justify-end h-full" title="{{ $dia }}: {{ Dinero::cop($valor) }}">
                        <div class="w-full bg-black rounded-t-sm" style="height: {{ max(2, round($valor / $maxDia * 100)) }}%"></div>
                        <span class="text-[10px] text-stone-500 mt-1">{{ $dia }}</span>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-stone-500 mt-3">
                Mes: hospedaje {{ Dinero::cop($ingresosPorTipo['hospedaje']) }} · restaurante {{ Dinero::cop($ingresosPorTipo['restaurante']) }}
            </p>
        </div>

        <div class="card p-4">
            <h3 class="text-sm font-semibold text-stone-500 uppercase mb-3">Mantenimiento</h3>
            <p class="text-3xl font-extrabold">{{ $mantenimientoAbierto }} <span class="text-sm font-medium text-stone-500">reportes abiertos</span></p>
            @if ($vencidas > 0)
                <a href="{{ route('panel.mantenimiento.index', ['vencidas' => 1]) }}" class="badge-retro px-2.5 py-0.5 text-xs bg-red-100 text-red-700 font-semibold mt-2">{{ $vencidas }} atrasado{{ $vencidas == 1 ? '' : 's' }}</a>
            @endif
            <p class="text-xs text-stone-500 mt-3">{{ $porEstado['mantenimiento'] }} habitación(es) fuera de servicio.</p>
            <a href="{{ route('panel.mantenimiento.index') }}" class="chip-retro mt-3">Ver reportes <span class="arrow">→</span></a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="card overflow-hidden">
            <div class="file-titlebar">
                <span class="dot"></span><span class="dot"></span>
                <span class="name">llegadas.hoy</span>
            </div>
            <ul class="divide-y divide-stone-100 text-sm">
                @forelse ($llegadas as $r)
                    <li class="px-4 py-2 flex items-center justify-between gap-2">
                        <span>{{ $r->huesped->nombre }} <span class="text-stone-500">· Hab. {{ $r->habitacion->numero }} ({{ Etiquetas::ESTADOS_HABITACION[$r->habitacion->estado] }})</span></span>
                        <a href="{{ route('panel.reservas.show', $r) }}" class="chip-retro">Check-in <span class="arrow">→</span></a>
                    </li>
                @empty
                    <li class="px-4 py-3 text-stone-400">No hay llegadas pendientes.</li>
                @endforelse
            </ul>
        </div>
        <div class="card overflow-hidden">
            <div class="file-titlebar">
                <span class="dot"></span><span class="dot"></span>
                <span class="name">salidas.hoy</span>
            </div>
            <ul class="divide-y divide-stone-100 text-sm">
                @forelse ($salidas as $r)
                    <li class="px-4 py-2 flex items-center justify-between gap-2">
                        <span>{{ $r->huesped->nombre }} <span class="text-stone-500">· Hab. {{ $r->habitacion->numero }}{{ $r->fecha_salida->isPast() && ! $r->fecha_salida->isToday() ? ' · vencida' : '' }}</span></span>
                        <a href="{{ route('panel.reservas.show', $r) }}" class="chip-retro">Check-out <span class="arrow">→</span></a>
                    </li>
                @empty
                    <li class="px-4 py-3 text-stone-400">No hay salidas para hoy.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card p-4">
            <h3 class="text-sm font-semibold text-stone-500 uppercase mb-3">Aseo del mes por persona</h3>
            <ul class="divide-y divide-stone-100 text-sm">
                @forelse ($rankingAseo as $fila)
                    <li class="py-2 flex items-center justify-between">
                        <span>{{ $fila->nombre }}</span>
                        <span class="text-stone-600"><strong>{{ $fila->total }}</strong> habitaciones · {{ $fila->promedio !== null ? $fila->promedio.' min prom.' : '—' }}</span>
                    </li>
                @empty
                    <li class="py-2 text-stone-400">Sin personal de aseo registrado.</li>
                @endforelse
            </ul>
            <a href="{{ route('panel.aseo.personal') }}" class="text-xs underline decoration-black/40 underline-offset-2 font-medium mt-2 inline-block">Ver registro completo</a>
        </div>
        <div class="card p-4">
            <h3 class="text-sm font-semibold text-stone-500 uppercase mb-3">Despachos del mes por persona</h3>
            <ul class="divide-y divide-stone-100 text-sm">
                @forelse ($rankingDespachos as $u)
                    <li class="py-2 flex items-center justify-between">
                        <span>{{ $u->nombre }} <span class="text-xs text-stone-500">{{ $u->cargo }}</span></span>
                        <span class="text-stone-600"><strong>{{ $u->total }}</strong> pedidos</span>
                    </li>
                @empty
                    <li class="py-2 text-stone-400">Sin despachos este mes.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
