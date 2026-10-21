{{-- Factura de venta: mismo lenguaje visual del sistema (ventana de archivo, bordes negros) y QR de verificación. --}}
@php
    use App\Support\Dinero;
    use App\Support\Etiquetas;
    $anulada = $factura->estado === 'anulada';
@endphp

<div id="documento" class="card overflow-hidden relative">
    <div class="file-titlebar">
        <span class="dot"></span><span class="dot"></span>
        <span class="name">{{ strtolower($factura->numero) }}.factura</span>
        <span class="ml-auto badge-retro px-2 py-0.5 text-xs {{ $anulada ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">{{ $anulada ? 'Anulada' : 'Emitida' }}</span>
    </div>

    @if ($anulada)
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
            <span class="text-7xl font-extrabold text-red-600/20 -rotate-12 border-8 border-red-600/20 px-6 rounded-xl">ANULADA</span>
        </div>
    @endif

    <div class="p-6 sm:p-8">
        {{-- Encabezado: hotel a la izquierda, número de factura y QR a la derecha --}}
        <div class="flex flex-col sm:flex-row items-start justify-between gap-6 pb-5 border-b-2 border-black">
            <div class="flex items-start gap-3">
                <span class="inline-flex w-12 h-12 items-center justify-center rounded-md bg-amber-400 border-2 border-black text-black text-2xl font-extrabold shadow-hard-sm">H</span>
                <div>
                    <p class="text-xl font-extrabold tracking-tight">{{ config('hotel.nombre') }}</p>
                    <p class="text-xs text-stone-600">NIT {{ config('hotel.nit') }}</p>
                    <p class="text-xs text-stone-600">{{ config('hotel.direccion') }}</p>
                    <p class="text-xs text-stone-600">{{ config('hotel.telefono') }} · {{ config('hotel.correo') }}</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="text-right">
                    <p class="text-xs uppercase tracking-wider text-stone-500 font-semibold">Factura de venta</p>
                    <p class="text-2xl font-extrabold font-mono">{{ $factura->numero }}</p>
                    <p class="text-xs text-stone-600 mt-1">Fecha: {{ $factura->created_at->format('d/m/Y H:i') }}</p>
                    <p class="text-xs text-stone-600">{{ $factura->tipo === 'hospedaje' ? 'Hospedaje y consumos' : 'Restaurante' }}</p>
                </div>
                <div class="w-24 h-24 shrink-0 border-2 border-black rounded-md bg-white p-1">
                    {!! \App\Support\Qr::svg(route('facturas.publica', $factura)) !!}
                </div>
            </div>
        </div>

        {{-- Cliente --}}
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm py-4 border-b border-stone-200">
            <div class="col-span-2">
                <dt class="text-xs text-stone-500">Cliente</dt>
                <dd class="font-semibold">{{ $factura->cliente_nombre }}</dd>
            </div>
            <div>
                <dt class="text-xs text-stone-500">Documento</dt>
                <dd class="font-semibold">{{ $factura->cliente_documento ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-stone-500">Forma de pago</dt>
                <dd class="font-semibold">{{ Etiquetas::FORMAS_PAGO[$factura->forma_pago] ?? $factura->forma_pago }}</dd>
            </div>
            @if ($factura->reserva)
                <div class="col-span-2">
                    <dt class="text-xs text-stone-500">Estadía</dt>
                    <dd class="font-semibold">{{ $factura->reserva->codigo }} · Habitación {{ $factura->reserva->habitacion->numero }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-xs text-stone-500">Fechas</dt>
                    <dd class="font-semibold">{{ $factura->reserva->fecha_entrada->format('d/m/Y') }} → {{ $factura->reserva->fecha_salida->format('d/m/Y') }} ({{ $factura->reserva->noches() }} noche{{ $factura->reserva->noches() == 1 ? '' : 's' }})</dd>
                </div>
            @endif
        </dl>

        {{-- Detalle --}}
        <table class="w-full text-sm mt-4">
            <thead class="text-left text-xs uppercase text-white bg-black">
                <tr>
                    <th class="px-3 py-2">Concepto</th>
                    <th class="px-3 py-2 text-center w-16">Cant.</th>
                    <th class="px-3 py-2 text-right w-32">Valor unit.</th>
                    <th class="px-3 py-2 text-right w-32">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200 border-x-2 border-b-2 border-black">
                @foreach ($factura->items as $item)
                    <tr class="even:bg-stone-50">
                        <td class="px-3 py-2">{{ $item->concepto }}</td>
                        <td class="px-3 py-2 text-center">{{ rtrim(rtrim(number_format((float) $item->cantidad, 2, ',', '.'), '0'), ',') }}</td>
                        <td class="px-3 py-2 text-right">{{ Dinero::cop($item->precio_unitario) }}</td>
                        <td class="px-3 py-2 text-right">{{ Dinero::cop($item->subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totales --}}
        <div class="flex justify-end mt-4">
            <dl class="w-full sm:w-72 text-sm border-2 border-black rounded-md overflow-hidden">
                <div class="flex justify-between px-3 py-1.5">
                    <dt>Subtotal</dt><dd>{{ Dinero::cop($factura->subtotal) }}</dd>
                </div>
                <div class="flex justify-between px-3 py-1.5 border-t border-stone-200">
                    <dt>IVA ({{ rtrim(rtrim(number_format(config('hotel.iva'), 2, ',', ''), '0'), ',') }}%)</dt><dd>{{ Dinero::cop($factura->iva) }}</dd>
                </div>
                <div class="flex justify-between px-3 py-2 bg-amber-300 border-t-2 border-black font-extrabold text-base">
                    <dt>Total</dt><dd>{{ Dinero::cop($factura->total) }}</dd>
                </div>
            </dl>
        </div>

        @if ($anulada)
            <p class="mt-4 text-sm text-red-700 font-semibold">Anulada: {{ $factura->motivo_anulacion }}</p>
        @endif

        <div class="mt-8 pt-3 border-t border-dashed border-stone-400 flex flex-wrap justify-between gap-2 text-[11px] text-stone-500">
            <span>Emitida por: {{ $factura->emitidaPor->nombre ?? 'Sistema' }}</span>
            <span>Escanea el QR para verificar esta factura en línea.</span>
            <span>Gracias por hospedarse con nosotros.</span>
        </div>
    </div>
</div>
