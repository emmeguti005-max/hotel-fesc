@extends($interna ? 'layouts.panel' : 'layouts.app')

@section('title', 'Factura '.$factura->numero)
@section('container', 'max-w-4xl')

@section('content')
    <div class="no-print flex flex-wrap items-center justify-between gap-3 mb-4">
        @if ($interna)
            <a href="{{ route('panel.facturas.index') }}" class="text-sm text-stone-500 hover:text-stone-700">&larr; Volver a facturas</a>
        @else
            <span class="text-sm text-stone-600">Factura verificada en el sistema de {{ config('hotel.nombre') }}.</span>
        @endif
        <button onclick="window.print()" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">Imprimir / guardar PDF</button>
    </div>

    @include('facturas._documento')

    @if ($interna && auth()->user()->isAdmin() && $factura->estado === 'emitida')
        <form method="POST" action="{{ route('panel.facturas.anular', $factura) }}" class="no-print card p-4 mt-6 flex flex-wrap items-center gap-2"
              onsubmit="return confirm('¿Anular la factura {{ $factura->numero }}?')">
            @csrf
            <span class="text-sm font-semibold">Anular factura</span>
            <input type="text" name="motivo" required maxlength="255" placeholder="Motivo de la anulación" class="flex-1 min-w-[220px] rounded-md input-retro text-sm">
            <button type="submit" class="px-4 py-2 rounded-md text-sm font-bold border-2 border-black bg-red-100 text-red-800 shadow-hard-sm hover:shadow-none">Anular</button>
        </form>
    @endif
@endsection
