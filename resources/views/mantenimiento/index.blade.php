@extends('layouts.panel')

@section('title', 'Mantenimiento')

@php
    $estadoBadge = [
        'pendiente' => 'bg-amber-100 text-amber-800',
        'en_proceso' => 'bg-blue-100 text-blue-800',
        'resuelto' => 'bg-green-100 text-green-800',
        'no_resuelto' => 'bg-red-100 text-red-800',
    ];
    $prioridadBadge = [
        'baja' => 'bg-stone-100 text-stone-600',
        'media' => 'bg-stone-100 text-stone-700',
        'alta' => 'bg-orange-100 text-orange-700',
        'urgente' => 'bg-red-100 text-red-700 font-semibold',
    ];
@endphp

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Reportes de mantenimiento</h2>
        <div class="flex items-center gap-4">
            @if ($vencidas > 0)
                <a href="{{ route('panel.mantenimiento.index', array_merge(request()->except('page'), ['vencidas' => 1])) }}"
                   class="badge-retro px-2.5 py-0.5 text-xs bg-red-100 text-red-700 font-semibold">
                    {{ $vencidas }} atrasada{{ $vencidas === 1 ? '' : 's' }}
                </a>
            @endif
            <span class="text-sm text-stone-500">{{ $solicitudes->total() }} en total</span>
        </div>
    </div>

    <form method="GET" class="card p-4 mb-6 grid grid-cols-2 sm:grid-cols-5 gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Estado</label>
            <select name="estado" onchange="this.form.submit()" class="w-full rounded-md input-retro text-sm">
                <option value="">Todos</option>
                @foreach (['pendiente', 'en_proceso', 'resuelto', 'no_resuelto'] as $estado)
                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Prioridad</label>
            <select name="prioridad" onchange="this.form.submit()" class="w-full rounded-md input-retro text-sm">
                <option value="">Todas</option>
                @foreach (['baja', 'media', 'alta', 'urgente'] as $prioridad)
                    <option value="{{ $prioridad }}" @selected(request('prioridad') === $prioridad)>{{ ucfirst($prioridad) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Torre</label>
            <select name="torre_id" onchange="this.form.submit()" class="w-full rounded-md input-retro text-sm">
                <option value="">Todas</option>
                @foreach ($torres as $torre)
                    <option value="{{ $torre->id }}" @selected((string) request('torre_id') === (string) $torre->id)>Torre {{ $torre->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Lugar</label>
            <select name="lugar" onchange="this.form.submit()" class="w-full rounded-md input-retro text-sm">
                <option value="">Todos</option>
                <option value="habitacion" @selected(request('lugar') === 'habitacion')>Habitaciones</option>
                <option value="zona" @selected(request('lugar') === 'zona')>Zonas comunes</option>
            </select>
        </div>

        <div class="flex items-center gap-3">
            <label class="flex items-center gap-1.5 text-sm text-stone-600">
                <input type="checkbox" name="vencidas" value="1" onchange="this.form.submit()" @checked(request()->boolean('vencidas'))>
                Solo atrasadas
            </label>

            @if (request()->anyFilled(['estado', 'prioridad', 'torre_id', 'lugar', 'vencidas']))
                <a href="{{ route('panel.mantenimiento.index') }}" class="text-sm text-stone-500 hover:text-stone-700">Limpiar filtros</a>
            @endif
        </div>
    </form>

    <div class="flex justify-end mb-3">
        <a href="{{ route('panel.mantenimiento.exportar', request()->query()) }}" class="text-sm text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium">
            Exportar CSV
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">mantenimiento.csv</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3">Prioridad</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Ubicación</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Descripción</th>
                    <th class="px-4 py-3">Técnico</th>
                    <th class="px-4 py-3">Reportado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($solicitudes as $solicitud)
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3">
                            <span class="badge-retro px-2.5 py-0.5 text-xs {{ $prioridadBadge[$solicitud->prioridad] }}">
                                {{ ucfirst($solicitud->prioridad) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge-retro px-2.5 py-0.5 text-xs {{ $estadoBadge[$solicitud->estado] }}">
                                {{ ucfirst(str_replace('_', ' ', $solicitud->estado)) }}
                            </span>
                            @if ($solicitud->estaVencida())
                                <span class="badge-retro px-2.5 py-0.5 text-xs bg-red-100 text-red-700 font-semibold ml-1">
                                    Atrasada
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-stone-600">{{ $solicitud->lugar() }}</td>
                        <td class="px-4 py-3 text-stone-600">{{ \App\Support\Etiquetas::TIPOS_SOLICITUD[$solicitud->tipo] ?? $solicitud->tipo }}</td>
                        <td class="px-4 py-3 text-stone-600 max-w-xs truncate">{{ $solicitud->descripcion }}</td>
                        <td class="px-4 py-3 text-stone-600">{{ $solicitud->tecnico->nombre ?? '—' }}</td>
                        <td class="px-4 py-3 text-stone-500">{{ $solicitud->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('panel.mantenimiento.show', $solicitud) }}" class="chip-retro">Ver <span class="arrow">→</span></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-stone-400">No hay reportes con estos filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $solicitudes->links() }}
    </div>
@endsection
