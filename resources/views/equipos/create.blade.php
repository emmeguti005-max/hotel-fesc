@extends('layouts.panel')

@section('title', 'Nuevo equipo')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Registrar activo</h2>
        <a href="{{ route('panel.equipos.index') }}" class="text-sm text-stone-500 hover:underline">← Volver al inventario</a>
    </div>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">nuevo-equipo.form</span>
        </div>
        <form method="POST" action="{{ route('panel.equipos.store') }}" class="p-6">
            @csrf
            @include('equipos._form')

            <button type="submit" class="mt-6 w-full sm:w-auto btn-retro px-6 py-2 rounded-md font-bold">
                Guardar equipo
            </button>
        </form>
    </div>
@endsection
