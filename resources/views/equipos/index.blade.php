@extends('layouts.panel')

@section('title', 'Activos')

@php use App\Support\Etiquetas; @endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h2 class="text-2xl font-extrabold tracking-tight">Inventario de activos</h2>
        <a href="{{ route('panel.equipos.create') }}" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">+ Registrar activo</a>
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
        @foreach (Etiquetas::TIPOS_EQUIPO as $tipo => $texto)
            @if ($totales->get($tipo))
                <a href="{{ route('panel.equipos.index', array_merge(request()->except(['tipo', 'page']), request('tipo') === $tipo ? [] : ['tipo' => $tipo])) }}"
                   class="chip-retro {{ request('tipo') === $tipo ? 'bg-amber-300' : '' }}">
                    @include('equipos._icono', ['tipo' => $tipo, 'class' => 'w-4 h-4'])
                    {{ $texto }} <span class="text-stone-500">{{ $totales->get($tipo) }}</span>
                </a>
            @endif
        @endforeach
    </div>

    <form method="GET" class="card p-3 mb-6 flex flex-wrap items-end gap-3">
        <input type="hidden" name="tipo" value="{{ request('tipo') }}">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-stone-500 mb-1">Buscar</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nombre, código, serie o habitación" class="w-full rounded-md input-retro text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Lugar</label>
            <select name="lugar" class="rounded-md input-retro text-sm">
                <option value="">Todos</option>
                <option value="habitacion" @selected(request('lugar') === 'habitacion')>Habitaciones</option>
                <option value="zona" @selected(request('lugar') === 'zona')>Zonas comunes</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Estado</label>
            <select name="estado" class="rounded-md input-retro text-sm">
                <option value="">Todos</option>
                <option value="activo" @selected(request('estado') === 'activo')>Activos</option>
                <option value="de_baja" @selected(request('estado') === 'de_baja')>De baja</option>
            </select>
        </div>
        <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">Filtrar</button>
    </form>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">activos.csv</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3">Código</th>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Ubicación</th>
                    <th class="px-4 py-3">Marca / modelo</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($equipos as $equipo)
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $equipo->codigo ?? '—' }}</td>
                        <td class="px-4 py-3 font-medium"><a href="{{ route('equipos.ficha', $equipo) }}" class="underline decoration-black/30 underline-offset-2">{{ $equipo->nombre }}</a></td>
                        <td class="px-4 py-3 text-stone-600">
                            <span class="inline-flex items-center gap-1.5">
                                @include('equipos._icono', ['tipo' => $equipo->tipo, 'class' => 'w-4 h-4'])
                                {{ Etiquetas::TIPOS_EQUIPO[$equipo->tipo] ?? $equipo->tipo }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-stone-600">{{ $equipo->lugar() }}</td>
                        <td class="px-4 py-3 text-stone-600">{{ trim(($equipo->marca ?? '').' '.($equipo->modelo ?? '')) ?: '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="badge-retro px-2.5 py-0.5 text-xs {{ $equipo->estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-stone-200 text-stone-600' }}">
                                {{ $equipo->estado === 'activo' ? 'Activo' : 'De baja' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                            <a href="{{ route('panel.equipos.edit', $equipo) }}" class="text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium">Editar</a>
                            @if (auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('panel.equipos.destroy', $equipo) }}" class="inline"
                                      onsubmit="return confirm('¿Eliminar este activo?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-stone-400">No hay activos con estos filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $equipos->links() }}
    </div>
@endsection
