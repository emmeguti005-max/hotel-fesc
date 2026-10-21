@extends('layouts.panel')

@section('title', 'Aseo')

@php
    use App\Support\Etiquetas;
    $yo = auth()->user();
@endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight">{{ $esSupervisor ? 'Aseo de habitaciones' : 'Mis tareas de aseo' }}</h2>
            <p class="text-sm text-stone-500">{{ $abiertas->count() }} abierta{{ $abiertas->count() == 1 ? '' : 's' }} · {{ $terminadasHoy->count() }} terminada{{ $terminadasHoy->count() == 1 ? '' : 's' }} hoy</p>
        </div>
        @if ($esSupervisor)
            <a href="{{ route('panel.aseo.personal') }}" class="chip-retro">Registro del personal <span class="arrow">→</span></a>
        @endif
    </div>

    @if ($esSupervisor)
        <form method="POST" action="{{ route('panel.aseo.store') }}" class="card p-4 mb-6 grid grid-cols-2 sm:grid-cols-6 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-medium text-stone-500 mb-1">Habitación</label>
                <select name="habitacion_id" required class="w-full rounded-md input-retro text-sm">
                    @foreach ($habitaciones as $h)
                        <option value="{{ $h->id }}">{{ $h->numero }} ({{ Etiquetas::ESTADOS_HABITACION[$h->estado] }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-stone-500 mb-1">Tipo</label>
                <select name="tipo" class="w-full rounded-md input-retro text-sm">
                    @foreach (Etiquetas::TIPOS_ASEO as $valor => $texto)
                        <option value="{{ $valor }}" @selected($valor === 'diaria')>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-stone-500 mb-1">Prioridad</label>
                <select name="prioridad" class="w-full rounded-md input-retro text-sm">
                    <option value="normal">Normal</option>
                    <option value="alta">Alta</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-stone-500 mb-1">Asignar a</label>
                <select name="asignada_a" class="w-full rounded-md input-retro text-sm">
                    <option value="">Quien la tome</option>
                    @foreach ($personal as $p)
                        <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-xs font-medium text-stone-500 mb-1">Indicaciones</label>
                <input type="text" name="notas" maxlength="500" class="w-full rounded-md input-retro text-sm">
            </div>
            <button type="submit" class="btn-retro px-3 py-2 rounded-md text-sm font-bold">+ Crear tarea</button>
        </form>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @forelse ($abiertas as $tarea)
            @php $mia = $tarea->asignada_a === $yo->id; @endphp
            <article class="card overflow-hidden {{ $tarea->prioridad === 'alta' ? 'ring-2 ring-red-500' : '' }}">
                <div class="file-titlebar">
                    <span class="dot"></span><span class="dot"></span>
                    <span class="name">hab-{{ $tarea->habitacion->numero }}.aseo</span>
                    <span class="ml-auto badge-retro px-2 py-0.5 text-xs {{ Etiquetas::COLOR_ASEO[$tarea->estado] }}">{{ Etiquetas::ESTADOS_ASEO[$tarea->estado] }}</span>
                </div>
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-2xl font-extrabold">{{ $tarea->habitacion->numero }}</p>
                            <p class="text-xs text-stone-500">{{ $tarea->habitacion->piso->etiqueta() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold">{{ Etiquetas::TIPOS_ASEO[$tarea->tipo] }}</p>
                            @if ($tarea->prioridad === 'alta')
                                <span class="badge-retro px-2 py-0.5 text-xs bg-red-100 text-red-700">Prioridad alta</span>
                            @endif
                        </div>
                    </div>

                    <p class="text-xs mt-2">
                        Habitación: <span class="font-semibold">{{ Etiquetas::ESTADOS_HABITACION[$tarea->habitacion->estado] }}</span>
                        @if ($tarea->habitacion->reservaActual)
                            · huésped {{ $tarea->habitacion->reservaActual->huesped->nombre }}
                        @endif
                    </p>
                    @if ($tarea->notas)
                        <p class="text-xs bg-amber-50 border border-amber-300 rounded px-2 py-1 mt-2">{{ $tarea->notas }}</p>
                    @endif
                    <p class="text-xs text-stone-500 mt-2">
                        {{ $tarea->asignadaA ? 'Asignada a '.$tarea->asignadaA->nombre : 'Sin asignar' }}
                        · {{ $tarea->inicio_at ? 'inició '.$tarea->inicio_at->format('H:i') : 'creada '.$tarea->created_at->diffForHumans() }}
                    </p>

                    <div class="mt-3 space-y-2">
                        @if ($tarea->estado === 'pendiente' && (! $tarea->asignada_a || $mia || $yo->isAdmin()) && $yo->tieneRol('aseo'))
                            <form method="POST" action="{{ route('panel.aseo.iniciar', $tarea) }}">
                                @csrf
                                <button type="submit" class="w-full btn-retro px-3 py-2 rounded-md text-sm font-bold">{{ $tarea->asignada_a ? 'Iniciar limpieza' : 'Tomar e iniciar' }}</button>
                            </form>
                        @endif

                        @if ($tarea->estado === 'en_proceso' && $mia)
                            <form method="POST" action="{{ route('panel.aseo.terminar', $tarea) }}" class="space-y-2">
                                @csrf
                                <input type="text" name="observaciones" maxlength="500" placeholder="Novedades (ej: falta toalla, control del TV dañado)" class="w-full rounded-md input-retro text-sm">
                                <button type="submit" class="w-full px-3 py-2 rounded-md text-sm font-bold bg-green-600 text-white border-2 border-black shadow-hard-sm hover:shadow-none">Terminar — habitación lista</button>
                            </form>
                        @endif

                        @if ($esSupervisor && $tarea->estado === 'pendiente')
                            <form method="POST" action="{{ route('panel.aseo.asignar', $tarea) }}" class="flex gap-2">
                                @csrf
                                <select name="asignada_a" class="flex-1 rounded-md input-retro text-xs">
                                    <option value="">Sin asignar</option>
                                    @foreach ($personal as $p)
                                        <option value="{{ $p->id }}" @selected($tarea->asignada_a === $p->id)>{{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="chip-retro">Asignar</button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="card p-6 text-sm text-stone-500 md:col-span-2 lg:col-span-3">No hay tareas de aseo abiertas. ¡Todo limpio!</div>
        @endforelse
    </div>

    <h3 class="text-lg font-extrabold tracking-tight mb-3">Terminadas hoy</h3>
    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">aseo-hoy.log</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">Habitación</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Personal</th>
                        <th class="px-4 py-3">Inicio</th>
                        <th class="px-4 py-3">Fin</th>
                        <th class="px-4 py-3">Duración</th>
                        <th class="px-4 py-3">Novedades</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($terminadasHoy as $tarea)
                        <tr>
                            <td class="px-4 py-2 font-semibold">{{ $tarea->habitacion->numero }}</td>
                            <td class="px-4 py-2">{{ Etiquetas::TIPOS_ASEO[$tarea->tipo] }}</td>
                            <td class="px-4 py-2">{{ $tarea->asignadaA->nombre ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $tarea->inicio_at?->format('H:i') }}</td>
                            <td class="px-4 py-2">{{ $tarea->fin_at?->format('H:i') }}</td>
                            <td class="px-4 py-2">{{ $tarea->minutos() }} min</td>
                            <td class="px-4 py-2 text-stone-600">{{ $tarea->observaciones ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-stone-400">Nada terminado hoy todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
