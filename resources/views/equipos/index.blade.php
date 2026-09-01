@extends('layouts.panel')

@section('title', 'Equipos')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Inventario de equipos</h2>
        <a href="{{ route('panel.equipos.create') }}" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">
            + Registrar equipo
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">equipos.csv</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Usuario</th>
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
                        <td class="px-4 py-3 font-medium">{{ $equipo->nombre }}</td>
                        <td class="px-4 py-3 text-stone-600">{{ $equipo->usuario_asignado ?: '—' }}</td>
                        <td class="px-4 py-3 text-stone-600">
                            <span class="inline-flex items-center gap-1.5">
                                @include('equipos._icono', ['tipo' => $equipo->tipo, 'class' => 'w-4 h-4'])
                                {{ ucfirst($equipo->tipo) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-stone-600">
                            Torre {{ $equipo->ubicacion->area->piso->torre->nombre }} ·
                            {{ $equipo->ubicacion->area->piso->numero == -1 ? 'Sótano' : 'Piso '.$equipo->ubicacion->area->piso->numero }} ·
                            {{ $equipo->ubicacion->area->nombre }} · {{ $equipo->ubicacion->nombre }}
                        </td>
                        <td class="px-4 py-3 text-stone-600">{{ trim(($equipo->marca ?? '').' '.($equipo->modelo ?? '')) ?: '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="badge-retro px-2.5 py-0.5 text-xs {{ $equipo->estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-stone-200 text-stone-600' }}">
                                {{ $equipo->estado === 'activo' ? 'Activo' : 'De baja' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('panel.equipos.edit', $equipo) }}" class="text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium">Editar</a>
                            <form method="POST" action="{{ route('panel.equipos.destroy', $equipo) }}" class="inline"
                                  onsubmit="return confirm('¿Eliminar este equipo?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-stone-400">Todavía no hay equipos registrados.</td>
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
