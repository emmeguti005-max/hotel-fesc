@extends('layouts.panel')

@section('title', 'Facturas')

@php
    use App\Support\Dinero;
    use App\Support\Etiquetas;
@endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Facturas</h2>
        <p class="text-sm text-stone-600">{{ $totales->cantidad }} emitida{{ $totales->cantidad == 1 ? '' : 's' }} · <span class="font-bold">{{ Dinero::cop($totales->total) }}</span></p>
    </div>

    <form method="GET" class="card p-4 mb-6 grid grid-cols-2 sm:grid-cols-5 gap-3 items-end">
        <div class="col-span-2 sm:col-span-1">
            <label class="block text-xs font-medium text-stone-500 mb-1">Buscar</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Número, cliente, documento" class="w-full rounded-md input-retro text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Tipo</label>
            <select name="tipo" class="w-full rounded-md input-retro text-sm">
                <option value="">Todos</option>
                <option value="hospedaje" @selected(request('tipo') === 'hospedaje')>Hospedaje</option>
                <option value="restaurante" @selected(request('tipo') === 'restaurante')>Restaurante</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Estado</label>
            <select name="estado" class="w-full rounded-md input-retro text-sm">
                <option value="">Todos</option>
                <option value="emitida" @selected(request('estado') === 'emitida')>Emitidas</option>
                <option value="anulada" @selected(request('estado') === 'anulada')>Anuladas</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Desde</label>
            <input type="date" name="desde" value="{{ request('desde') }}" class="w-full rounded-md input-retro text-sm">
        </div>
        <div class="flex items-end gap-2">
            <div class="flex-1">
                <label class="block text-xs font-medium text-stone-500 mb-1">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full rounded-md input-retro text-sm">
            </div>
            <button type="submit" class="btn-retro px-3 py-2 rounded-md text-sm font-bold">Filtrar</button>
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">facturas.csv</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">Número</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Pago</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($facturas as $factura)
                        <tr class="hover:bg-stone-50 {{ $factura->estado === 'anulada' ? 'text-stone-400 line-through' : '' }}">
                            <td class="px-4 py-3 font-mono font-semibold">{{ $factura->numero }}</td>
                            <td class="px-4 py-3">{{ $factura->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $factura->cliente_nombre }}</td>
                            <td class="px-4 py-3">{{ ucfirst($factura->tipo) }}</td>
                            <td class="px-4 py-3">{{ Etiquetas::FORMAS_PAGO[$factura->forma_pago] ?? $factura->forma_pago }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ Dinero::cop($factura->total) }}</td>
                            <td class="px-4 py-3 no-underline">
                                <span class="badge-retro px-2 py-0.5 text-xs {{ $factura->estado === 'anulada' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">{{ ucfirst($factura->estado) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('panel.facturas.show', $factura) }}" class="chip-retro">Ver <span class="arrow">→</span></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-stone-400">No hay facturas con estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $facturas->links() }}</div>
@endsection
