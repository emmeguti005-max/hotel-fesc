@extends('layouts.panel')

@section('title', 'Estructura · Torres')

@section('content')
    <h2 class="text-2xl font-extrabold tracking-tight mb-1">Estructura física</h2>
    <p class="text-sm text-stone-500 mb-6">Torres → Pisos → Áreas → Zonas comunes. Las habitaciones se gestionan en <a href="{{ route('panel.habitaciones.index') }}" class="underline decoration-black/40 underline-offset-2 font-medium text-black">Habitaciones</a>. Elimina solo lo que esté vacío.</p>

    <form method="POST" action="{{ route('panel.estructura.torres.store') }}" class="card p-4 mb-6 flex items-end gap-3">
        @csrf
        <div class="flex-1">
            <label class="block text-xs font-medium text-stone-500 mb-1">Nueva torre</label>
            <input type="text" name="nombre" required maxlength="50" placeholder="Ej: A, B, C"
                   class="w-full rounded-md input-retro text-sm">
        </div>
        <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">
            Agregar
        </button>
    </form>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">torres.csv</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3">Torre</th>
                    <th class="px-4 py-3">Pisos</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($torres as $torre)
                    <tr class="hover:bg-stone-50" x-data="{ editando: false }">
                        <td class="px-4 py-3">
                            <span x-show="!editando">Torre {{ $torre->nombre }}</span>
                            <form x-show="editando" method="POST" action="{{ route('panel.estructura.torres.update', $torre) }}" class="flex gap-2">
                                @csrf @method('PUT')
                                <input type="text" name="nombre" value="{{ $torre->nombre }}" required maxlength="50"
                                       class="rounded-md input-retro text-sm py-1">
                                <button type="submit" class="text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium text-xs">Guardar</button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-stone-600">{{ $torre->pisos_count }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('panel.estructura.pisos', $torre) }}" class="chip-retro">Ver pisos <span class="arrow">→</span></a>
                            <button type="button" @click="editando = !editando" class="text-stone-500 hover:underline">Editar</button>
                            <form method="POST" action="{{ route('panel.estructura.torres.destroy', $torre) }}" class="inline"
                                  onsubmit="return confirm('¿Eliminar esta torre?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-stone-400">Todavía no hay torres.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection
