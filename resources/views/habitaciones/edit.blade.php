@extends('layouts.panel')

@section('title', 'Editar habitación '.$habitacion->numero)

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Editar habitación {{ $habitacion->numero }}</h2>
        <a href="{{ route('panel.habitaciones.show', $habitacion) }}" class="text-sm text-stone-500 hover:underline">← Volver</a>
    </div>

    <div class="card overflow-hidden max-w-2xl">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">habitacion-{{ $habitacion->numero }}.edit</span>
        </div>
        <form method="POST" action="{{ route('panel.habitaciones.update', $habitacion) }}" class="p-6">
            @csrf
            @method('PUT')
            @include('habitaciones._form')
            <button type="submit" class="mt-6 btn-retro px-6 py-2 rounded-md font-bold">Guardar cambios</button>
        </form>
    </div>

    <form method="POST" action="{{ route('panel.habitaciones.destroy', $habitacion) }}" class="mt-6"
          onsubmit="return confirm('¿Eliminar la habitación {{ $habitacion->numero }}?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm text-red-600 hover:underline">Eliminar habitación</button>
    </form>
@endsection
