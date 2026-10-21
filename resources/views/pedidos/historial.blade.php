@extends('layouts.panel')

@section('title', 'Historial de pedidos')

@php
    use App\Support\Dinero;
    use App\Support\Etiquetas;
@endphp

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Historial de pedidos</h2>
        <a href="{{ route('panel.pedidos.index') }}" class="text-sm text-stone-500 hover:underline">← Volver a cocina</a>
    </div>

    <form method="GET" class="card p-4 mb-6 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Estado</label>
            <select name="estado" onchange="this.form.submit()" class="rounded-md input-retro text-sm">
                <option value="">Entregados y cancelados</option>
                <option value="entregado" @selected(request('estado') === 'entregado')>Entregados</option>
                <option value="cancelado" @selected(request('estado') === 'cancelado')>Cancelados</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Fecha</label>
            <input type="date" name="fecha" value="{{ request('fecha') }}" onchange="this.form.submit()" class="rounded-md input-retro text-sm">
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">despachos.log</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">Pedido</th>
                        <th class="px-4 py-3">Destino</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Despachó</th>
                        <th class="px-4 py-3">Entregado</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3">Pago</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($pedidos as $pedido)
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-mono font-semibold">{{ $pedido->codigo }}</td>
                            <td class="px-4 py-3">{{ $pedido->destino() }}</td>
                            <td class="px-4 py-3"><span class="badge-retro px-2 py-0.5 text-xs {{ Etiquetas::COLOR_PEDIDO[$pedido->estado] }}">{{ Etiquetas::ESTADOS_PEDIDO[$pedido->estado] }}</span></td>
                            <td class="px-4 py-3 text-stone-600">{{ $pedido->despachadoPor->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-stone-600">{{ $pedido->entregado_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">{{ Dinero::cop($pedido->total) }}</td>
                            <td class="px-4 py-3 text-stone-600">
                                {{ Etiquetas::FORMAS_PAGO[$pedido->forma_pago] }}
                                @if ($pedido->factura)
                                    · <a href="{{ route('panel.facturas.show', $pedido->factura) }}" class="underline decoration-black/40 underline-offset-2">{{ $pedido->factura->numero }}</a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('panel.pedidos.show', $pedido) }}" class="chip-retro">Despacho <span class="arrow">→</span></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-stone-400">No hay pedidos con estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $pedidos->links() }}</div>
@endsection
