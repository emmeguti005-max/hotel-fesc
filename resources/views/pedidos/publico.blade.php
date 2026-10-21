@extends('layouts.app')

@section('title', 'Pedido '.$pedido->codigo)

@section('content')
    @if ($pedido->estaActivo())
        {{-- Mientras el pedido avanza, la página se refresca sola para mostrar el estado --}}
        <meta http-equiv="refresh" content="30">
    @endif

    @include('pedidos._despacho')

    <div class="no-print flex flex-wrap items-center justify-between gap-3 mt-6">
        @if ($pedido->habitacion)
            <a href="{{ route('portal.habitacion', $pedido->habitacion) }}" class="text-sm text-stone-600 hover:underline">&larr; Volver a mi habitación</a>
        @else
            <span></span>
        @endif
        <button onclick="window.print()" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">Imprimir</button>
    </div>
@endsection
