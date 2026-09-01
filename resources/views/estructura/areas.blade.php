@extends('layouts.panel')

@section('title', 'Estructura · Áreas')

@section('content')
    <nav class="flex items-center mb-4">
        <a href="{{ route('panel.estructura.torres') }}" class="tab-folder">Torres</a>
        <a href="{{ route('panel.estructura.pisos', $torre) }}" class="tab-folder">Torre {{ $torre->nombre }}</a>
        <span class="tab-folder is-current">{{ $piso->numero == -1 ? 'Sótano' : 'Piso '.$piso->numero }}</span>
    </nav>
    <h2 class="text-2xl font-extrabold tracking-tight mb-6">
        Áreas — Torre {{ $torre->nombre }}, {{ $piso->numero == -1 ? 'Sótano' : 'Piso '.$piso->numero }}
    </h2>

    <form method="POST" action="{{ route('panel.estructura.areas.store', [$torre, $piso]) }}" class="card p-4 mb-6 flex items-end gap-3">
        @csrf
        <div class="flex-1">
            <label class="block text-xs font-medium text-stone-500 mb-1">Nueva área</label>
            <input type="text" name="nombre" required maxlength="100" placeholder="Ej: Radiología, Enfermería"
                   class="w-full rounded-md input-retro text-sm">
        </div>
        <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">
            Agregar
        </button>
    </form>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">areas.csv</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3">Área</th>
                    <th class="px-4 py-3">Ubicaciones</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($areas as $area)
                    <tr class="hover:bg-stone-50" x-data="{ editando: false }">
                        <td class="px-4 py-3">
                            <span x-show="!editando">{{ $area->nombre }}</span>
                            <form x-show="editando" method="POST" action="{{ route('panel.estructura.areas.update', [$torre, $piso, $area]) }}" class="flex gap-2">
                                @csrf @method('PUT')
                                <input type="text" name="nombre" value="{{ $area->nombre }}" required maxlength="100"
                                       class="rounded-md input-retro text-sm py-1">
                                <button type="submit" class="text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium text-xs">Guardar</button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-stone-600">{{ $area->ubicaciones_count }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('panel.estructura.ubicaciones', [$torre, $piso, $area]) }}" class="chip-retro">Ver ubicaciones <span class="arrow">→</span></a>
                            <button type="button" @click="editando = !editando" class="text-stone-500 hover:underline">Editar</button>
                            <form method="POST" action="{{ route('panel.estructura.areas.destroy', [$torre, $piso, $area]) }}" class="inline"
                                  onsubmit="return confirm('¿Eliminar esta área?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-stone-400">Todavía no hay áreas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection
