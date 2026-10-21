@extends('layouts.panel')

@section('title', 'Nueva habitación')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Nueva habitación</h2>
        <a href="{{ route('panel.habitaciones.index') }}" class="text-sm text-stone-500 hover:underline">← Tablero</a>
    </div>

    <div class="card overflow-hidden max-w-2xl">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">habitacion.nueva</span>
        </div>
        <form method="POST" action="{{ route('panel.habitaciones.store') }}" class="p-6">
            @csrf
            @include('habitaciones._form')
            <button type="submit" class="mt-6 btn-retro px-6 py-2 rounded-md font-bold">Crear habitación</button>
        </form>
    </div>
@endsection
