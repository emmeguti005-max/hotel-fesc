@extends('layouts.panel')

@section('title', 'Reserva '.$reserva->codigo)

@php
    use App\Support\Dinero;
    use App\Support\Etiquetas;
@endphp

@section('content')
    <a href="{{ route('panel.reservas.index') }}" class="text-sm text-stone-500 hover:text-stone-700">&larr; Volver a reservas</a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-4">
        <div class="lg:col-span-2 space-y-6">
            <div class="card overflow-hidden">
                <div class="file-titlebar">
                    <span class="dot"></span><span class="dot"></span>
                    <span class="name">{{ strtolower($reserva->codigo) }}.estadia</span>
                    <span class="ml-auto badge-retro px-2 py-0.5 text-xs {{ Etiquetas::COLOR_RESERVA[$reserva->estado] }}">{{ Etiquetas::ESTADOS_RESERVA[$reserva->estado] }}</span>
                </div>
                <div class="p-6">
                    <h2 class="text-2xl font-extrabold tracking-tight">{{ $reserva->huesped->nombre }}</h2>
                    <p class="text-sm text-stone-500">{{ $reserva->huesped->tipo_documento }} {{ $reserva->huesped->documento }} · {{ $reserva->huesped->telefono ?? 'sin teléfono' }} · {{ $reserva->huesped->ciudad ?? '' }} {{ $reserva->huesped->pais }}</p>

                    <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm mt-5 border-t border-b border-stone-100 py-4">
                        <div>
                            <dt class="text-stone-400">Habitación</dt>
                            <dd class="font-semibold"><a href="{{ route('panel.habitaciones.show', $reserva->habitacion) }}" class="underline decoration-black/40 underline-offset-2">{{ $reserva->habitacion->numero }}</a> · {{ Etiquetas::TIPOS_HABITACION[$reserva->habitacion->tipo] }}</dd>
                        </div>
                        <div>
                            <dt class="text-stone-400">Entrada</dt>
                            <dd class="font-semibold">{{ $reserva->fecha_entrada->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-stone-400">Salida</dt>
                            <dd class="font-semibold">{{ $reserva->fecha_salida->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-stone-400">Noches</dt>
                            <dd class="font-semibold">{{ $reserva->noches() }}</dd>
                        </div>
                        <div>
                            <dt class="text-stone-400">Personas</dt>
                            <dd class="font-semibold">{{ $reserva->adultos }} adulto(s){{ $reserva->ninos ? ', '.$reserva->ninos.' niño(s)' : '' }}</dd>
                        </div>
                        <div>
                            <dt class="text-stone-400">Tarifa / noche</dt>
                            <dd class="font-semibold">{{ Dinero::cop($reserva->tarifa_noche) }}</dd>
                        </div>
                        <div>
                            <dt class="text-stone-400">Check-in</dt>
                            <dd class="font-semibold">{{ $reserva->checkin_at?->format('d/m H:i') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-stone-400">Check-out</dt>
                            <dd class="font-semibold">{{ $reserva->checkout_at?->format('d/m H:i') ?? '—' }}</dd>
                        </div>
                    </dl>

                    @if ($reserva->observaciones)
                        <p class="text-sm mt-4"><span class="text-stone-500">Observaciones:</span> {{ $reserva->observaciones }}</p>
                    @endif
                    <p class="text-xs text-stone-400 mt-3">Creada por {{ $reserva->creadaPor->nombre ?? 'sistema' }} el {{ $reserva->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="file-titlebar">
                    <span class="dot"></span><span class="dot"></span>
                    <span class="name">consumos-restaurante.log</span>
                </div>
                <div class="p-4">
                    @if ($reserva->estado === 'en_curso')
                        <a href="{{ route('panel.pedidos.create', ['habitacion_id' => $reserva->habitacion_id]) }}" class="chip-retro mb-3">+ Pedido a la habitación</a>
                    @endif
                    @if ($reserva->pedidos->isEmpty())
                        <p class="text-sm text-stone-400">Sin consumos del restaurante.</p>
                    @else
                        <ul class="divide-y divide-stone-100 text-sm">
                            @foreach ($reserva->pedidos as $pedido)
                                <li class="py-2 flex flex-wrap items-center justify-between gap-2">
                                    <span>
                                        <a href="{{ route('panel.pedidos.show', $pedido) }}" class="font-mono font-semibold underline decoration-black/30 underline-offset-2">{{ $pedido->codigo }}</a>
                                        <span class="text-stone-500">· {{ $pedido->created_at->format('d/m H:i') }} · {{ $pedido->items->map(fn ($i) => $i->cantidad.'× '.$i->descripcion)->implode(', ') }}</span>
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <span class="text-xs text-stone-500">{{ Etiquetas::FORMAS_PAGO[$pedido->forma_pago] }}</span>
                                        <span class="badge-retro px-2 py-0.5 text-xs {{ Etiquetas::COLOR_PEDIDO[$pedido->estado] }}">{{ Etiquetas::ESTADOS_PEDIDO[$pedido->estado] }}</span>
                                        <span class="font-semibold">{{ Dinero::cop($pedido->total) }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card overflow-hidden">
                <div class="file-titlebar">
                    <span class="dot"></span><span class="dot"></span>
                    <span class="name">cuenta.resumen</span>
                </div>
                <div class="p-5 text-sm">
                    @if ($reserva->factura)
                        <p class="mb-3">Facturada en <span class="font-mono font-bold">{{ $reserva->factura->numero }}</span>.</p>
                        <p class="text-2xl font-extrabold mb-4">{{ Dinero::cop($reserva->factura->total) }}</p>
                        <a href="{{ route('panel.facturas.show', $reserva->factura) }}" class="btn-retro px-4 py-2 rounded-md text-sm font-bold w-full">Ver factura</a>
                    @else
                        <dl class="space-y-1">
                            <div class="flex justify-between"><dt>Hospedaje ({{ $reserva->noches() }} noches)</dt><dd>{{ Dinero::cop($reserva->totalHospedaje()) }}</dd></div>
                            <div class="flex justify-between"><dt>Consumos entregados</dt><dd>{{ Dinero::cop($estimado['consumos']) }}</dd></div>
                            <div class="flex justify-between"><dt>IVA {{ config('hotel.iva') }}%</dt><dd>{{ Dinero::cop($estimado['iva']) }}</dd></div>
                            <div class="flex justify-between border-t-2 border-black pt-2 mt-2 text-base font-extrabold"><dt>Total estimado</dt><dd>{{ Dinero::cop($estimado['total']) }}</dd></div>
                        </dl>
                    @endif
                </div>
            </div>

            @if ($reserva->estado === 'reservada')
                <div class="card p-5 space-y-3">
                    <form method="POST" action="{{ route('panel.reservas.checkin', $reserva) }}">
                        @csrf
                        <button type="submit" class="w-full btn-retro px-4 py-2 rounded-md font-bold">Hacer check-in</button>
                    </form>
                    <p class="text-xs text-stone-500">La habitación debe estar en estado <strong>disponible</strong> (limpia).
                        Ahora está: <strong>{{ Etiquetas::ESTADOS_HABITACION[$reserva->habitacion->estado] }}</strong>.</p>
                    <form method="POST" action="{{ route('panel.reservas.cancelar', $reserva) }}" onsubmit="return confirm('¿Cancelar la reserva {{ $reserva->codigo }}?')">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:underline">Cancelar reserva</button>
                    </form>
                </div>
            @elseif ($reserva->estado === 'en_curso')
                <form method="POST" action="{{ route('panel.reservas.checkout', $reserva) }}" class="card p-5 space-y-3"
                      onsubmit="return confirm('¿Confirmar check-out y emitir la factura?')">
                    @csrf
                    <p class="font-bold">Check-out</p>
                    <p class="text-xs text-stone-500">Emite la factura (hospedaje + consumos), libera la habitación y crea la tarea de aseo de salida.</p>
                    <select name="forma_pago" required class="w-full rounded-md input-retro text-sm">
                        @foreach (\Illuminate\Support\Arr::except(Etiquetas::FORMAS_PAGO, ['cargo_habitacion']) as $valor => $texto)
                            <option value="{{ $valor }}">{{ $texto }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full btn-retro px-4 py-2 rounded-md font-bold">Check-out y facturar</button>
                </form>
            @endif
        </div>
    </div>
@endsection
