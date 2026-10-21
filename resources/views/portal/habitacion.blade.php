@extends('layouts.app')

@section('title', 'Habitación '.$habitacion->numero)

@php
    use App\Support\Dinero;
    use App\Support\Etiquetas;
    $catalogo = $productos->flatten()->map(fn ($p) => ['id' => $p->id, 'precio' => (float) $p->precio])->values();
@endphp

@section('content')
    <div class="card overflow-hidden mb-6">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">habitacion-{{ $habitacion->numero }}.portal</span>
        </div>
        <div class="p-6">
            <p class="text-xs uppercase tracking-wider text-stone-500 font-semibold">{{ $habitacion->piso->etiqueta() }}</p>
            <h2 class="text-3xl font-extrabold tracking-tight">Habitación {{ $habitacion->numero }}</h2>
            @if ($reserva)
                <p class="text-stone-600 mt-1">Hola, <strong>{{ \Illuminate\Support\Str::of($reserva->huesped->nombre)->before(' ') }}</strong>. Tu salida es el {{ $reserva->fecha_salida->format('d/m/Y') }} a las {{ config('hotel.hora_checkout') }}.</p>
            @else
                <p class="text-stone-600 mt-1">Esta habitación no tiene una estadía activa en este momento. Igual puedes reportar un daño.</p>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-100 border-2 border-black text-red-900 text-sm p-3 font-medium shadow-hard-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($reserva)
        {{-- Pedido al restaurante con carrito en Alpine; el total final lo calcula el servidor --}}
        <form method="POST" action="{{ route('portal.pedir', $habitacion->token) }}" class="card overflow-hidden mb-6"
              x-data="carrito({{ \Illuminate\Support\Js::from($catalogo) }})">
            @csrf
            <div class="file-titlebar">
                <span class="dot"></span><span class="dot"></span>
                <span class="name">servicio-a-la-habitacion.menu</span>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-extrabold tracking-tight mb-1">Pedir al restaurante</h3>
                <p class="text-sm text-stone-500 mb-4">Se carga a la cuenta de tu habitación y lo pagas al hacer check-out.</p>

                @foreach ($productos as $categoria => $items)
                    <p class="text-xs uppercase tracking-wider text-stone-500 font-semibold mt-4 mb-2">{{ Etiquetas::CATEGORIAS_PRODUCTO[$categoria] ?? $categoria }}</p>
                    <ul class="divide-y divide-stone-100 border-2 border-black rounded-md bg-white">
                        @foreach ($items as $producto)
                            <li class="flex items-center justify-between gap-3 px-3 py-2">
                                <div class="min-w-0">
                                    <p class="font-medium text-sm">{{ $producto->nombre }}</p>
                                    @if ($producto->descripcion)
                                        <p class="text-xs text-stone-500 truncate">{{ $producto->descripcion }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-sm text-stone-600">{{ Dinero::cop($producto->precio) }}</span>
                                    <button type="button" @click="menos({{ $producto->id }})" class="w-7 h-7 rounded border-2 border-black font-bold leading-none">−</button>
                                    <input type="number" min="0" max="20" name="cantidades[{{ $producto->id }}]" x-model.number="cant[{{ $producto->id }}]"
                                           class="w-12 text-center rounded-md input-retro text-sm py-0.5">
                                    <button type="button" @click="mas({{ $producto->id }})" class="w-7 h-7 rounded border-2 border-black font-bold leading-none bg-amber-300">+</button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endforeach

                <label class="block text-sm font-medium mt-4 mb-1">Indicaciones (opcional)</label>
                <input type="text" name="notas" maxlength="300" placeholder="Ej: sin cebolla, traer cubiertos para 2"
                       class="w-full rounded-md input-retro text-sm">

                <div class="flex items-center justify-between gap-4 mt-5">
                    <p class="text-sm">Total aprox. <span class="font-extrabold text-lg" x-text="formato(total)"></span> <span class="text-xs text-stone-500">+ IVA</span></p>
                    <button type="submit" class="btn-retro px-6 py-2 rounded-md font-bold" :disabled="total === 0">Enviar pedido</button>
                </div>
            </div>
        </form>

        <script>
            function carrito(catalogo) {
                const cant = {};
                catalogo.forEach(p => cant[p.id] = 0);
                return {
                    cant,
                    mas(id) { this.cant[id] = Math.min(20, (this.cant[id] || 0) + 1); },
                    menos(id) { this.cant[id] = Math.max(0, (this.cant[id] || 0) - 1); },
                    get total() { return catalogo.reduce((s, p) => s + p.precio * (this.cant[p.id] || 0), 0); },
                    formato(v) { return '$ ' + Math.round(v).toLocaleString('es-CO'); },
                };
            }
        </script>

        @if ($pedidos->isNotEmpty())
            <div class="card overflow-hidden mb-6">
                <div class="file-titlebar">
                    <span class="dot"></span><span class="dot"></span>
                    <span class="name">mis-pedidos.log</span>
                </div>
                <ul class="divide-y divide-stone-100 text-sm">
                    @foreach ($pedidos as $pedido)
                        <li class="px-4 py-3 flex items-center justify-between gap-3">
                            <span>
                                <span class="font-semibold">{{ $pedido->codigo }}</span>
                                <span class="text-stone-500">· {{ $pedido->created_at->format('d/m H:i') }} · {{ Dinero::cop($pedido->total) }}</span>
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="badge-retro px-2 py-0.5 text-xs {{ Etiquetas::COLOR_PEDIDO[$pedido->estado] }}">{{ Etiquetas::ESTADOS_PEDIDO[$pedido->estado] }}</span>
                                <a href="{{ route('pedidos.publico', $pedido) }}" class="chip-retro">Ver <span class="arrow">→</span></a>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('portal.aseo', $habitacion->token) }}" class="card overflow-hidden mb-6">
            @csrf
            <div class="file-titlebar">
                <span class="dot"></span><span class="dot"></span>
                <span class="name">solicitar-aseo.form</span>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-extrabold tracking-tight mb-1">Solicitar aseo</h3>
                @if ($aseoPendiente)
                    <p class="text-sm text-cyan-800 bg-cyan-50 border-2 border-black rounded-md p-3">Ya hay una limpieza programada para tu habitación.</p>
                @else
                    <p class="text-sm text-stone-500 mb-3">Toallas, tendido de cama, limpieza general…</p>
                    <div class="flex flex-wrap gap-2">
                        <input type="text" name="notas" maxlength="300" placeholder="¿Algo en especial? (opcional)" class="flex-1 min-w-[200px] rounded-md input-retro text-sm">
                        <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">Pedir aseo</button>
                    </div>
                @endif
            </div>
        </form>
    @endif

    <form method="POST" action="{{ route('portal.reportar', $habitacion->token) }}" class="card overflow-hidden">
        @csrf
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">reportar-dano.form</span>
        </div>
        <div class="p-6 space-y-3">
            <h3 class="text-xl font-extrabold tracking-tight">Reportar un daño</h3>
            <select name="tipo" required class="w-full rounded-md input-retro">
                @foreach (\Illuminate\Support\Arr::except(Etiquetas::TIPOS_SOLICITUD, ['equipo_cocina']) as $valor => $texto)
                    <option value="{{ $valor }}">{{ $texto }}</option>
                @endforeach
            </select>
            <textarea name="descripcion" rows="3" required maxlength="1000" placeholder="Ej: el aire acondicionado no enfría" class="w-full rounded-md input-retro"></textarea>
            <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">Enviar reporte</button>
        </div>
    </form>
@endsection
