@extends('layouts.panel')

@section('title', 'Cocina')

@push('head')
    {{-- El tablero se actualiza solo para ver los pedidos nuevos que llegan por QR --}}
    <meta http-equiv="refresh" content="45">
@endpush

@php
    use App\Support\Dinero;
    use App\Support\Etiquetas;
    $columnas = ['recibido', 'en_preparacion', 'listo', 'despachado'];
@endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight">Cocina y despacho</h2>
            <p class="text-sm text-stone-500">Hoy: {{ $entregadosHoy }} pedido{{ $entregadosHoy == 1 ? '' : 's' }} entregado{{ $entregadosHoy == 1 ? '' : 's' }} · {{ Dinero::cop($ventasHoy) }} en ventas</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('panel.pedidos.historial') }}" class="chip-retro">Historial</a>
            <a href="{{ route('panel.pedidos.create') }}" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">+ Nuevo pedido</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @foreach ($columnas as $estado)
            @php $lista = $pedidos->get($estado, collect()); @endphp
            <section class="cabinet">
                <div class="flex items-center justify-between mb-3">
                    <span class="tab-folder is-current">{{ Etiquetas::ESTADOS_PEDIDO[$estado] }}</span>
                    <span class="text-xs font-bold">{{ $lista->count() }}</span>
                </div>

                @forelse ($lista as $pedido)
                    <article class="bg-white border-2 border-black rounded-md p-3 mb-3 last:mb-0 shadow-hard-sm">
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ route('panel.pedidos.show', $pedido) }}" class="font-mono font-bold text-sm underline decoration-black/30 underline-offset-2">{{ $pedido->codigo }}</a>
                            <span class="text-[11px] text-stone-500" title="{{ $pedido->created_at->format('d/m H:i') }}">{{ $pedido->created_at->diffForHumans(null, true) }}</span>
                        </div>
                        <p class="text-sm font-semibold mt-1">{{ $pedido->destino() }}</p>
                        <ul class="text-xs text-stone-700 mt-1 space-y-0.5">
                            @foreach ($pedido->items as $item)
                                <li><span class="font-bold">{{ $item->cantidad }}×</span> {{ $item->descripcion }}</li>
                            @endforeach
                        </ul>
                        @if ($pedido->notas)
                            <p class="text-xs bg-amber-50 border border-amber-300 rounded px-1.5 py-1 mt-2">{{ $pedido->notas }}</p>
                        @endif
                        @if ($pedido->despachadoPor)
                            <p class="text-[11px] text-stone-500 mt-1">Lleva: {{ $pedido->despachadoPor->nombre }}</p>
                        @endif
                        <div class="flex flex-wrap items-center justify-between gap-2 mt-3">
                            <span class="text-xs font-semibold">{{ Dinero::cop($pedido->total) }}{{ $pedido->forma_pago === 'cargo_habitacion' ? ' · a la hab.' : '' }}</span>
                            <div class="flex items-center gap-1">
                                @include('pedidos._acciones', ['pedido' => $pedido])
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-xs text-stone-600 px-1">Sin pedidos.</p>
                @endforelse
            </section>
        @endforeach
    </div>
@endsection
