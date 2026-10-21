@extends('layouts.app')

@section('title', 'Bienvenido')

@section('content')
    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">bienvenida.hotel</span>
        </div>
        <div class="p-6">
            <h2 class="text-3xl font-extrabold tracking-tight mb-1">{{ config('hotel.nombre') }}</h2>
            <p class="text-sm text-stone-500 mb-6">{{ config('hotel.direccion') }} · {{ config('hotel.telefono') }}</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-md border-2 border-black p-4 bg-amber-50">
                    <p class="font-bold mb-1">¿Estás hospedado?</p>
                    <p class="text-sm text-stone-600">
                        Escanea el código QR que está en tu habitación. Desde ahí puedes pedir al restaurante
                        (se carga a tu cuenta), solicitar aseo o reportar un daño.
                    </p>
                </div>
                <div class="rounded-md border-2 border-black p-4">
                    <p class="font-bold mb-1">¿Viste algo dañado?</p>
                    <p class="text-sm text-stone-600 mb-3">Lobby, piscina, ascensores, parqueadero… avísanos y mantenimiento lo revisa.</p>
                    <a href="{{ route('solicitudes.create') }}" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">Reportar un daño</a>
                </div>
            </div>
        </div>
    </div>

    <p class="text-center text-xs text-stone-500 mt-6">
        ¿Eres parte del equipo? <a href="{{ route('login') }}" class="underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium text-black">Ingresa al panel</a>.
    </p>
@endsection
