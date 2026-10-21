@extends('layouts.panel')

@section('title', 'Pedido '.$pedido->codigo)

@section('content')
    <div class="no-print flex items-center justify-between mb-4">
        <a href="{{ route('panel.pedidos.index') }}" class="text-sm text-stone-500 hover:text-stone-700">&larr; Volver a cocina</a>
        <div class="flex items-center gap-2">
            @include('pedidos._acciones', ['pedido' => $pedido])
            <button onclick="window.print()" class="chip-retro">Imprimir despacho</button>
        </div>
    </div>

    @include('pedidos._despacho')

    @if ($pedido->reserva)
        <p class="no-print text-sm text-stone-500 mt-4">
            Estadía: <a href="{{ route('panel.reservas.show', $pedido->reserva) }}" class="underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium text-black">{{ $pedido->reserva->codigo }}</a>
            · {{ $pedido->reserva->huesped->nombre }}
        </p>
    @endif
@endsection
