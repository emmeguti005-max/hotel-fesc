@extends('layouts.panel')

@section('title', 'Estructura · Ubicaciones')

@php
    $tipos = ['consultorio', 'oficina', 'pasillo', 'sala', 'deposito', 'otro'];
@endphp

@section('content')
    <nav class="flex items-center mb-4">
        <a href="{{ route('panel.estructura.torres') }}" class="tab-folder">Torres</a>
        <a href="{{ route('panel.estructura.pisos', $torre) }}" class="tab-folder">Torre {{ $torre->nombre }}</a>
        <a href="{{ route('panel.estructura.areas', [$torre, $piso]) }}" class="tab-folder">{{ $piso->numero == -1 ? 'Sótano' : 'Piso '.$piso->numero }}</a>
        <span class="tab-folder is-current">{{ $area->nombre }}</span>
    </nav>
    <h2 class="text-2xl font-extrabold tracking-tight mb-6">Ubicaciones — {{ $area->nombre }}</h2>

    <form method="POST" action="{{ route('panel.estructura.ubicaciones.store', [$torre, $piso, $area]) }}" class="card p-4 mb-6 grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
        @csrf
        <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-stone-500 mb-1">Nueva ubicación</label>
            <input type="text" name="nombre" required maxlength="100" placeholder="Ej: Consultorio 304"
                   class="w-full rounded-md input-retro text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Tipo</label>
            <select name="tipo" required class="w-full rounded-md input-retro text-sm">
                @foreach ($tipos as $tipo)
                    <option value="{{ $tipo }}">{{ ucfirst($tipo) }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-3">
            <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">
                Agregar
            </button>
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">ubicaciones.csv</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3">Ubicación</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Equipos</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($ubicaciones as $ubicacion)
                    <tr class="hover:bg-stone-50" x-data="{ editando: false }">
                        <td class="px-4 py-3">
                            <span x-show="!editando">{{ $ubicacion->nombre }}</span>
                            <form x-show="editando" method="POST" action="{{ route('panel.estructura.ubicaciones.update', [$torre, $piso, $area, $ubicacion]) }}" class="flex gap-2">
                                @csrf @method('PUT')
                                <input type="text" name="nombre" value="{{ $ubicacion->nombre }}" required maxlength="100"
                                       class="rounded-md input-retro text-sm py-1">
                                <select name="tipo" class="rounded-md input-retro text-sm py-1">
                                    @foreach ($tipos as $tipo)
                                        <option value="{{ $tipo }}" @selected($ubicacion->tipo === $tipo)>{{ ucfirst($tipo) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium text-xs">Guardar</button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-stone-600" x-show="!editando">{{ ucfirst($ubicacion->tipo) }}</td>
                        <td class="px-4 py-3 text-stone-600" x-show="editando"></td>
                        <td class="px-4 py-3 text-stone-600">{{ $ubicacion->equipos_count }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <button type="button" @click="editando = !editando" class="text-stone-500 hover:underline">Editar</button>
                            <form method="POST" action="{{ route('panel.estructura.ubicaciones.destroy', [$torre, $piso, $area, $ubicacion]) }}" class="inline"
                                  onsubmit="return confirm('¿Eliminar esta ubicación?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-stone-400">Todavía no hay ubicaciones.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection
