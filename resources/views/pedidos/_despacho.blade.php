{{-- Registro de despacho: documento imprimible del pedido, con QR a su versión pública. --}}
@php
    use App\Support\Dinero;
    use App\Support\Etiquetas;
    $pasos = [
        ['Recibido', $pedido->created_at, $pedido->tomadoPor?->nombre ?? ($pedido->origen === 'habitacion' ? 'Huésped (QR)' : null)],
        ['En preparación', $pedido->preparacion_at, $pedido->preparadoPor?->nombre],
        ['Listo', $pedido->listo_at, $pedido->preparadoPor?->nombre],
        ['Despachado', $pedido->despachado_at, $pedido->despachadoPor?->nombre],
        ['Entregado', $pedido->entregado_at, $pedido->recibido_por ? 'Recibió: '.$pedido->recibido_por : null],
    ];
@endphp

<div id="documento" class="card overflow-hidden">
    <div class="file-titlebar">
        <span class="dot"></span><span class="dot"></span>
        <span class="name">{{ strtolower($pedido->codigo) }}.despacho</span>
        <span class="ml-auto badge-retro px-2 py-0.5 text-xs {{ Etiquetas::COLOR_PEDIDO[$pedido->estado] }}">{{ Etiquetas::ESTADOS_PEDIDO[$pedido->estado] }}</span>
    </div>

    <div class="p-6">
        <div class="flex items-start justify-between gap-6 border-b-2 border-black pb-4 mb-4">
            <div>
                <p class="text-xs uppercase tracking-wider text-stone-500 font-semibold">{{ config('hotel.nombre') }} · Restaurante</p>
                <h2 class="text-2xl font-extrabold tracking-tight">Registro de despacho</h2>
                <p class="text-sm text-stone-600 mt-1"><span class="font-mono font-bold">{{ $pedido->codigo }}</span> · {{ $pedido->created_at->format('d/m/Y H:i') }}</p>
                <p class="text-sm mt-2">
                    <span class="font-semibold">{{ $pedido->destino() }}</span>
                    @if ($pedido->habitacion)
                        <span class="text-stone-500">· {{ $pedido->habitacion->piso->etiqueta() }}</span>
                    @endif
                </p>
                <p class="text-sm text-stone-600">{{ Etiquetas::ORIGENES_PEDIDO[$pedido->origen] }} · {{ Etiquetas::FORMAS_PAGO[$pedido->forma_pago] }}</p>
            </div>
            <div class="w-28 h-28 shrink-0 border-2 border-black rounded-md bg-white p-1">
                {!! \App\Support\Qr::svg(route('pedidos.publico', $pedido)) !!}
            </div>
        </div>

        <table class="w-full text-sm mb-4">
            <thead class="text-left text-stone-500 text-xs uppercase border-b border-stone-200">
                <tr>
                    <th class="py-2">Producto</th>
                    <th class="py-2 text-center w-16">Cant.</th>
                    <th class="py-2 text-right w-28">Valor unit.</th>
                    <th class="py-2 text-right w-28">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach ($pedido->items as $item)
                    <tr>
                        <td class="py-2">{{ $item->descripcion }}</td>
                        <td class="py-2 text-center">{{ $item->cantidad }}</td>
                        <td class="py-2 text-right">{{ Dinero::cop($item->precio_unitario) }}</td>
                        <td class="py-2 text-right">{{ Dinero::cop($item->subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-black">
                    <td colspan="3" class="py-2 text-right font-bold">Total (antes de IVA)</td>
                    <td class="py-2 text-right font-extrabold">{{ Dinero::cop($pedido->total) }}</td>
                </tr>
            </tfoot>
        </table>

        @if ($pedido->notas)
            <p class="text-sm mb-4"><span class="text-stone-500">Indicaciones:</span> {{ $pedido->notas }}</p>
        @endif

        <p class="text-xs uppercase tracking-wider text-stone-500 font-semibold mb-2">Trazabilidad</p>
        <ol class="grid grid-cols-1 sm:grid-cols-5 gap-2 mb-4">
            @foreach ($pasos as [$titulo, $fecha, $quien])
                <li class="rounded-md border-2 border-black p-2 text-xs {{ $fecha ? 'bg-amber-50' : 'bg-stone-50 text-stone-400' }}">
                    <p class="font-bold">{{ $titulo }}</p>
                    <p>{{ $fecha?->format('d/m H:i') ?? '—' }}</p>
                    @if ($quien)
                        <p class="truncate">{{ $quien }}</p>
                    @endif
                </li>
            @endforeach
        </ol>

        @if ($pedido->estado === 'cancelado')
            <p class="text-sm font-semibold text-red-700">Pedido cancelado.</p>
        @elseif ($pedido->forma_pago === 'cargo_habitacion')
            <p class="text-sm text-stone-600">
                Cargado a la cuenta de la habitación{{ $pedido->factura ? '' : ': se cobra en la factura del check-out' }}.
                @if ($pedido->factura)
                    Facturado en <a href="{{ route('facturas.publica', $pedido->factura) }}" class="underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium text-black">{{ $pedido->factura->numero }}</a>.
                @endif
            </p>
        @elseif ($pedido->factura)
            <p class="text-sm text-stone-600">
                Factura: <a href="{{ route('facturas.publica', $pedido->factura) }}" class="underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium text-black">{{ $pedido->factura->numero }}</a>
                ({{ Dinero::cop($pedido->factura->total) }} con IVA)
            </p>
        @endif

        <div class="grid grid-cols-2 gap-8 mt-10 text-xs text-stone-500">
            <div class="border-t border-black pt-1">Despachó: {{ $pedido->despachadoPor?->nombre ?? '________________' }}</div>
            <div class="border-t border-black pt-1">Recibió: {{ $pedido->recibido_por ?? '________________' }}</div>
        </div>
    </div>
</div>
