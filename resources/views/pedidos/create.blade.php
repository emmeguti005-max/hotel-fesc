@extends('layouts.panel')

@section('title', 'Nuevo pedido')

@php
    use App\Support\Dinero;
    use App\Support\Etiquetas;
    $catalogo = $productos->flatten()->map(fn ($p) => ['id' => $p->id, 'precio' => (float) $p->precio])->values();
    $inicial = [
        'origen' => old('origen', $habitacionElegida ? 'habitacion' : 'mesa'),
        'forma_pago' => old('forma_pago', $habitacionElegida ? 'cargo_habitacion' : 'efectivo'),
    ];
@endphp

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Nuevo pedido</h2>
        <a href="{{ route('panel.pedidos.index') }}" class="text-sm text-stone-500 hover:underline">← Volver a cocina</a>
    </div>

    <form method="POST" action="{{ route('panel.pedidos.store') }}"
          x-data="pedidoForm({{ \Illuminate\Support\Js::from($catalogo) }}, {{ \Illuminate\Support\Js::from($inicial) }})"
          class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf

        <div class="lg:col-span-2 card overflow-hidden">
            <div class="file-titlebar">
                <span class="dot"></span><span class="dot"></span>
                <span class="name">menu.carta</span>
            </div>
            <div class="p-4">
                @foreach ($productos as $categoria => $items)
                    <p class="text-xs uppercase tracking-wider text-stone-500 font-semibold mt-3 first:mt-0 mb-2">{{ Etiquetas::CATEGORIAS_PRODUCTO[$categoria] ?? $categoria }}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($items as $producto)
                            <div class="flex items-center justify-between gap-2 border-2 border-black rounded-md px-3 py-2"
                                 :class="cant[{{ $producto->id }}] > 0 ? 'bg-amber-100' : 'bg-white'">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium truncate">{{ $producto->nombre }}</p>
                                    <p class="text-xs text-stone-500">{{ Dinero::cop($producto->precio) }}</p>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" @click="menos({{ $producto->id }})" class="w-7 h-7 rounded border-2 border-black font-bold leading-none bg-white">−</button>
                                    <input type="number" min="0" max="50" name="cantidades[{{ $producto->id }}]" x-model.number="cant[{{ $producto->id }}]"
                                           class="w-11 text-center rounded-md input-retro text-sm py-0.5">
                                    <button type="button" @click="mas({{ $producto->id }})" class="w-7 h-7 rounded border-2 border-black font-bold leading-none bg-amber-300">+</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card overflow-hidden self-start">
            <div class="file-titlebar">
                <span class="dot"></span><span class="dot"></span>
                <span class="name">pedido.nuevo</span>
            </div>
            <div class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Origen</label>
                    <select name="origen" x-model="origen" @change="if (origen !== 'habitacion' && forma_pago === 'cargo_habitacion') forma_pago = 'efectivo'" class="w-full rounded-md input-retro">
                        @foreach (Etiquetas::ORIGENES_PEDIDO as $valor => $texto)
                            <option value="{{ $valor }}">{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="origen === 'habitacion'">
                    <label class="block text-sm font-medium mb-1">Habitación (ocupadas)</label>
                    <select name="habitacion_id" :disabled="origen !== 'habitacion'" class="w-full rounded-md input-retro">
                        <option value="">Selecciona...</option>
                        @foreach ($ocupadas as $habitacion)
                            <option value="{{ $habitacion->id }}" @selected((int) old('habitacion_id', $habitacionElegida) === $habitacion->id)>
                                {{ $habitacion->numero }} — {{ $habitacion->reservaActual?->huesped?->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-show="origen === 'mesa'">
                    <label class="block text-sm font-medium mb-1">Mesa</label>
                    <input type="text" name="mesa" value="{{ old('mesa') }}" maxlength="20" :disabled="origen !== 'mesa'" placeholder="Ej: 4" class="w-full rounded-md input-retro">
                </div>

                <div x-show="origen !== 'habitacion'">
                    <label class="block text-sm font-medium mb-1">Cliente (opcional)</label>
                    <input type="text" name="cliente_nombre" value="{{ old('cliente_nombre') }}" maxlength="120" placeholder="Para la factura" class="w-full rounded-md input-retro">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Forma de pago</label>
                    <select name="forma_pago" x-model="forma_pago" class="w-full rounded-md input-retro">
                        @foreach (Etiquetas::FORMAS_PAGO as $valor => $texto)
                            <option value="{{ $valor }}" @if ($valor === 'cargo_habitacion') :disabled="origen !== 'habitacion'" @endif>{{ $texto }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-stone-500 mt-1" x-show="forma_pago === 'cargo_habitacion'">Se cobra en la factura del check-out.</p>
                    <p class="text-xs text-stone-500 mt-1" x-show="forma_pago !== 'cargo_habitacion'">La factura se emite al confirmar la entrega.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Indicaciones</label>
                    <textarea name="notas" rows="2" maxlength="500" class="w-full rounded-md input-retro text-sm">{{ old('notas') }}</textarea>
                </div>

                <div class="border-t-2 border-black pt-3 flex items-center justify-between">
                    <span class="text-sm">Total <span class="text-xs text-stone-500">(+ IVA)</span></span>
                    <span class="text-xl font-extrabold" x-text="formato(total)"></span>
                </div>

                <button type="submit" class="w-full btn-retro px-4 py-2 rounded-md font-bold" :disabled="total === 0">Enviar a cocina</button>
            </div>
        </div>
    </form>

    <script>
        function pedidoForm(catalogo, inicial) {
            const cant = {};
            catalogo.forEach(p => cant[p.id] = 0);
            return {
                cant,
                origen: inicial.origen,
                forma_pago: inicial.forma_pago,
                mas(id) { this.cant[id] = Math.min(50, (this.cant[id] || 0) + 1); },
                menos(id) { this.cant[id] = Math.max(0, (this.cant[id] || 0) - 1); },
                get total() { return catalogo.reduce((s, p) => s + p.precio * (this.cant[p.id] || 0), 0); },
                formato(v) { return '$ ' + Math.round(v).toLocaleString('es-CO'); },
            };
        }
    </script>
@endsection
