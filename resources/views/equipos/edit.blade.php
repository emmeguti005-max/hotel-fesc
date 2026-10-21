@extends('layouts.panel')

@section('title', 'Editar activo')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Editar activo</h2>
        <a href="{{ route('panel.equipos.index') }}" class="text-sm text-stone-500 hover:underline">← Volver al inventario de activos</a>
    </div>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">equipo-{{ $equipo->id }}.edit</span>
        </div>
        <form method="POST" action="{{ route('panel.equipos.update', $equipo) }}" class="p-6">
            @csrf
            @method('PUT')
            @include('equipos._form')

            <button type="submit" class="mt-6 w-full sm:w-auto btn-retro px-6 py-2 rounded-md font-bold">
                Guardar cambios
            </button>
        </form>
    </div>

    @if ($equipo->mantenimientos()->exists())
        <h3 class="text-lg font-extrabold tracking-tight mt-8 mb-4">Hoja de vida — mantenimientos</h3>
        @foreach ($equipo->mantenimientos()->latest('fecha_cierre')->get() as $solicitud)
            <div class="card overflow-hidden mb-4">
                <div class="file-titlebar">
                    <span class="dot"></span><span class="dot"></span>
                    <span class="name">mantenimiento-{{ $solicitud->id }}.log</span>
                    <span class="ml-auto badge-retro px-2 py-0.5 text-xs {{ $solicitud->estado === 'resuelto' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700' }}">
                        {{ ucfirst(str_replace('_', ' ', $solicitud->estado)) }}
                    </span>
                </div>
                <div class="p-4 flex items-center justify-between gap-3">
                    <div>
                        <span class="font-medium text-sm">{{ \App\Support\Etiquetas::TIPOS_SOLICITUD[$solicitud->tipo] ?? $solicitud->tipo }}</span>
                        <span class="text-stone-500 text-sm">— {{ $solicitud->descripcion }}</span>
                    </div>
                    <a href="{{ route('panel.mantenimiento.show', $solicitud) }}" class="chip-retro">Ver <span class="arrow">→</span></a>
                </div>
            </div>
        @endforeach
    @endif
@endsection
