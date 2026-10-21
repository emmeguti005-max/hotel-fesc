@extends('layouts.panel')

@section('title', 'Estructura · Pisos')

@section('content')
    <nav class="flex items-center mb-4">
        <a href="{{ route('panel.estructura.torres') }}" class="tab-folder">Torres</a>
        <span class="tab-folder is-current">Torre {{ $torre->nombre }}</span>
    </nav>
    <h2 class="text-2xl font-extrabold tracking-tight mb-6">Pisos de la Torre {{ $torre->nombre }}</h2>

    <form method="POST" action="{{ route('panel.estructura.pisos.store', $torre) }}" class="card p-4 mb-6 flex items-end gap-3">
        @csrf
        <div class="flex-1">
            <label class="block text-xs font-medium text-stone-500 mb-1">Nuevo piso</label>
            <select name="numero" required class="w-full rounded-md input-retro text-sm">
                @unless ($sotano)
                    <option value="-1">Sótano (compartido por las 3 torres)</option>
                @endunless
                @for ($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}">Piso {{ $i }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">
            Agregar
        </button>
    </form>

    @if ($sotano && ! $pisos->contains('numero', -1))
        <p class="text-xs text-stone-500 mb-6">
            El sótano ya existe y es compartido por las tres torres — se administra desde
            <a href="{{ route('panel.estructura.areas', [$sotano->torre, $sotano]) }}" class="text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium">Torre {{ $sotano->torre->nombre }}</a>.
        </p>
    @endif

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">pisos.csv</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3">Piso</th>
                    <th class="px-4 py-3">Habitaciones</th>
                    <th class="px-4 py-3">Zonas comunes</th>
                    <th class="px-4 py-3">Plano real</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($pisos as $piso)
                    <tr class="hover:bg-stone-50" x-data="{ subiendo: false }">
                        <td class="px-4 py-3">{{ $piso->numero == -1 ? 'Sótano (compartido)' : 'Piso '.$piso->numero }}</td>
                        <td class="px-4 py-3 text-stone-600">{{ $piso->habitaciones_count }}</td>
                        <td class="px-4 py-3 text-stone-600">{{ $piso->areas_count }}</td>
                        <td class="px-4 py-3">
                            <div x-show="!subiendo" class="flex items-center gap-3">
                                @if ($piso->plano_imagen)
                                    <img src="{{ asset('storage/'.$piso->plano_imagen) }}" alt="Plano" class="w-12 h-9 object-cover rounded border border-stone-200">
                                @else
                                    <span class="text-stone-400 text-xs">Sin plano</span>
                                @endif
                                <button type="button" @click="subiendo = true" class="text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium text-xs">
                                    {{ $piso->plano_imagen ? 'Reemplazar' : 'Subir' }}
                                </button>
                                @if ($piso->plano_imagen)
                                    <form method="POST" action="{{ route('panel.estructura.pisos.plano.destroy', [$torre, $piso]) }}"
                                          onsubmit="return confirm('¿Quitar el plano de este piso?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-xs">Quitar</button>
                                    </form>
                                @endif
                            </div>
                            <form x-show="subiendo" method="POST" action="{{ route('panel.estructura.pisos.plano.store', [$torre, $piso]) }}"
                                  enctype="multipart/form-data" class="flex items-center gap-2">
                                @csrf
                                <input type="file" name="plano" accept="image/png,image/jpeg,image/webp" required class="text-xs">
                                <button type="submit" class="text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium text-xs">Guardar</button>
                                <button type="button" @click="subiendo = false" class="text-stone-400 hover:underline text-xs">Cancelar</button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('panel.estructura.areas', [$torre, $piso]) }}" class="chip-retro">Zonas comunes <span class="arrow">→</span></a>
                            <form method="POST" action="{{ route('panel.estructura.pisos.destroy', [$torre, $piso]) }}" class="inline"
                                  onsubmit="return confirm('¿Eliminar este piso?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-stone-400">Todavía no hay pisos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection
