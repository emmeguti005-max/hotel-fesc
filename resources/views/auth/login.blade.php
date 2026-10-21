@extends('layouts.app')

@section('title', 'Ingresar')

@section('content')
    <div class="card overflow-hidden max-w-sm mx-auto">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">acceso.session</span>
        </div>
        <div class="p-6">
        <h2 class="text-2xl font-extrabold tracking-tight mb-1">Acceso del personal</h2>
        <p class="text-sm text-stone-500 mb-6">Recepción, aseo, restaurante, mantenimiento y administración.</p>

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-100 border-2 border-black text-red-900 text-sm p-3 font-medium shadow-hard-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Correo</label>
                <input type="email" name="correo" value="{{ old('correo') }}" required autofocus
                       class="w-full rounded-md input-retro">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Contraseña</label>
                <input type="password" name="password" required class="w-full rounded-md input-retro">
            </div>

            <label class="flex items-center gap-2 text-sm text-stone-600">
                <input type="checkbox" name="recordar" class="rounded input-retro">
                Recordarme
            </label>

            <button type="submit" class="w-full btn-retro px-6 py-2 rounded-md font-bold">
                Ingresar
            </button>
        </form>
        </div>
    </div>
@endsection
