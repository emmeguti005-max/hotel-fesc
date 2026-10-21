@extends('layouts.panel')

@section('title', 'Menú del restaurante')

@php
    use App\Support\Dinero;
    use App\Support\Etiquetas;
@endphp

@section('content')
    <h2 class="text-2xl font-extrabold tracking-tight mb-6">Menú del restaurante</h2>

    <form method="POST" action="{{ route('panel.productos.store') }}" class="card p-4 mb-8 grid grid-cols-2 sm:grid-cols-6 gap-3 items-end">
        @csrf
        <div class="col-span-2">
            <label class="block text-xs font-medium text-stone-500 mb-1">Producto</label>
            <input type="text" name="nombre" required maxlength="120" class="w-full rounded-md input-retro text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Categoría</label>
            <select name="categoria" class="w-full rounded-md input-retro text-sm">
                @foreach (Etiquetas::CATEGORIAS_PRODUCTO as $valor => $texto)
                    <option value="{{ $valor }}">{{ $texto }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Precio</label>
            <input type="number" name="precio" required min="0" step="100" class="w-full rounded-md input-retro text-sm">
        </div>
        <div class="col-span-2 sm:col-span-1">
            <label class="block text-xs font-medium text-stone-500 mb-1">Descripción</label>
            <input type="text" name="descripcion" maxlength="255" class="w-full rounded-md input-retro text-sm">
        </div>
        <button type="submit" class="btn-retro px-3 py-2 rounded-md text-sm font-bold">+ Agregar</button>
    </form>

    @foreach ($productos as $categoria => $items)
        <span class="tab-folder is-current">{{ Etiquetas::CATEGORIAS_PRODUCTO[$categoria] ?? $categoria }}</span>
        <div class="card overflow-hidden mb-6 mt-2">
            <ul class="divide-y divide-stone-100">
                @foreach ($items as $producto)
                    <li class="px-4 py-3" x-data="{ editar: false }">
                        <div x-show="!editar" class="flex flex-wrap items-center justify-between gap-3">
                            <div class="{{ $producto->disponible ? '' : 'opacity-50' }}">
                                <p class="font-medium">{{ $producto->nombre }} @unless ($producto->disponible)<span class="badge-retro px-2 py-0.5 text-xs bg-stone-200 ml-1">Agotado</span>@endunless</p>
                                <p class="text-xs text-stone-500">{{ $producto->descripcion }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold">{{ Dinero::cop($producto->precio) }}</span>
                                <form method="POST" action="{{ route('panel.productos.alternar', $producto) }}">
                                    @csrf
                                    <button type="submit" class="chip-retro">{{ $producto->disponible ? 'Marcar agotado' : 'Disponible' }}</button>
                                </form>
                                <button type="button" @click="editar = true" class="text-sm underline decoration-black/40 underline-offset-2">Editar</button>
                            </div>
                        </div>
                        <form x-show="editar" x-cloak method="POST" action="{{ route('panel.productos.update', $producto) }}" class="grid grid-cols-2 sm:grid-cols-6 gap-2 items-end">
                            @csrf
                            @method('PUT')
                            <input type="text" name="nombre" value="{{ $producto->nombre }}" required maxlength="120" class="col-span-2 rounded-md input-retro text-sm">
                            <select name="categoria" class="rounded-md input-retro text-sm">
                                @foreach (Etiquetas::CATEGORIAS_PRODUCTO as $valor => $texto)
                                    <option value="{{ $valor }}" @selected($producto->categoria === $valor)>{{ $texto }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="precio" value="{{ (int) $producto->precio }}" required min="0" step="100" class="rounded-md input-retro text-sm">
                            <input type="text" name="descripcion" value="{{ $producto->descripcion }}" maxlength="255" class="rounded-md input-retro text-sm">
                            <div class="flex gap-2">
                                <button type="submit" class="btn-retro px-3 py-1.5 rounded-md text-xs font-bold">Guardar</button>
                                <button type="button" @click="editar = false" class="text-xs">Cancelar</button>
                            </div>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
@endsection
