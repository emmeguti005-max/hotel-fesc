@extends('layouts.panel')

@section('title', 'Nueva reserva')

@php
    use App\Support\Dinero;
    use App\Support\Etiquetas;
    $infoHabitaciones = $habitaciones->mapWithKeys(fn ($h) => [$h->id => ['tarifa' => (float) $h->tarifa_noche, 'capacidad' => $h->capacidad]]);
@endphp

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Nueva reserva</h2>
        <a href="{{ route('panel.reservas.index') }}" class="text-sm text-stone-500 hover:underline">← Volver a reservas</a>
    </div>

    <form method="POST" action="{{ route('panel.reservas.store') }}" class="grid grid-cols-1 lg:grid-cols-2 gap-6"
          x-data="reservaForm({{ \Illuminate\Support\Js::from($huespedes->keyBy('documento')) }}, {{ \Illuminate\Support\Js::from($infoHabitaciones) }})">
        @csrf

        <div class="card overflow-hidden">
            <div class="file-titlebar">
                <span class="dot"></span><span class="dot"></span>
                <span class="name">huesped.ficha</span>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipo doc.</label>
                        <select name="huesped[tipo_documento]" x-model="h.tipo_documento" class="w-full rounded-md input-retro">
                            @foreach (['CC', 'CE', 'PAS', 'NIT', 'TI'] as $tipo)
                                <option value="{{ $tipo }}">{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium mb-1">Documento</label>
                        <input type="text" name="huesped[documento]" x-model="h.documento" @blur="buscar()" required maxlength="30" class="w-full rounded-md input-retro">
                    </div>
                </div>
                <p x-show="encontrado" x-cloak class="text-xs bg-green-50 border-2 border-black rounded-md px-2 py-1">Huésped registrado: se cargaron sus datos.</p>

                <div>
                    <label class="block text-sm font-medium mb-1">Nombre completo</label>
                    <input type="text" name="huesped[nombre]" x-model="h.nombre" required maxlength="120" class="w-full rounded-md input-retro">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Teléfono</label>
                        <input type="text" name="huesped[telefono]" x-model="h.telefono" maxlength="30" class="w-full rounded-md input-retro">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Correo</label>
                        <input type="email" name="huesped[correo]" x-model="h.correo" maxlength="120" class="w-full rounded-md input-retro">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ciudad</label>
                        <input type="text" name="huesped[ciudad]" x-model="h.ciudad" maxlength="80" class="w-full rounded-md input-retro">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">País</label>
                        <input type="text" name="huesped[pais]" x-model="h.pais" maxlength="80" class="w-full rounded-md input-retro">
                    </div>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="file-titlebar">
                <span class="dot"></span><span class="dot"></span>
                <span class="name">estadia.reserva</span>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Habitación</label>
                    <select name="habitacion_id" x-model="habitacion_id" required class="w-full rounded-md input-retro">
                        <option value="">Selecciona...</option>
                        @foreach ($habitaciones as $habitacion)
                            <option value="{{ $habitacion->id }}">
                                {{ $habitacion->numero }} · {{ Etiquetas::TIPOS_HABITACION[$habitacion->tipo] }} · {{ $habitacion->capacidad }} pers. · {{ Dinero::cop($habitacion->tarifa_noche) }} — {{ Etiquetas::ESTADOS_HABITACION[$habitacion->estado] }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-stone-500 mt-1">El sistema rechaza la reserva si la habitación ya está tomada en esas fechas.</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Entrada</label>
                        <input type="date" name="fecha_entrada" x-model="entrada" min="{{ today()->toDateString() }}" required class="w-full rounded-md input-retro">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Salida</label>
                        <input type="date" name="fecha_salida" x-model="salida" required class="w-full rounded-md input-retro">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Adultos</label>
                        <input type="number" name="adultos" value="{{ old('adultos', 1) }}" min="1" max="10" required class="w-full rounded-md input-retro">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Niños</label>
                        <input type="number" name="ninos" value="{{ old('ninos', 0) }}" min="0" max="10" class="w-full rounded-md input-retro">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Observaciones</label>
                    <textarea name="observaciones" rows="2" maxlength="1000" class="w-full rounded-md input-retro text-sm">{{ old('observaciones') }}</textarea>
                </div>

                <div class="border-2 border-black rounded-md p-3 bg-amber-50 text-sm" x-show="noches > 0 && tarifa > 0">
                    <span x-text="noches"></span> noche(s) × <span x-text="formato(tarifa)"></span> =
                    <span class="font-extrabold" x-text="formato(noches * tarifa)"></span> <span class="text-xs text-stone-500">+ IVA</span>
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="checkin_inmediato" value="1" @checked(old('checkin_inmediato'))>
                    Hacer check-in ahora (si la entrada es hoy y la habitación está lista)
                </label>

                <button type="submit" class="w-full btn-retro px-4 py-2 rounded-md font-bold">Guardar reserva</button>
            </div>
        </div>
    </form>

    <script>
        function reservaForm(huespedes, habitaciones) {
            const hoy = new Date().toISOString().slice(0, 10);
            const manana = new Date(Date.now() + 864e5).toISOString().slice(0, 10);
            return {
                h: {
                    tipo_documento: @js(old('huesped.tipo_documento', 'CC')),
                    documento: @js(old('huesped.documento', '')),
                    nombre: @js(old('huesped.nombre', '')),
                    telefono: @js(old('huesped.telefono', '')),
                    correo: @js(old('huesped.correo', '')),
                    ciudad: @js(old('huesped.ciudad', '')),
                    pais: @js(old('huesped.pais', 'Colombia')),
                },
                encontrado: false,
                habitacion_id: @js((string) old('habitacion_id', $habitacionElegida ?? '')),
                entrada: @js(old('fecha_entrada')) || hoy,
                salida: @js(old('fecha_salida')) || manana,
                buscar() {
                    const existente = huespedes[this.h.documento.trim()];
                    this.encontrado = !!existente;
                    if (existente) {
                        Object.keys(this.h).forEach(k => { if (existente[k]) this.h[k] = existente[k]; });
                    }
                },
                get tarifa() { return habitaciones[this.habitacion_id]?.tarifa ?? 0; },
                get noches() {
                    const d = (new Date(this.salida) - new Date(this.entrada)) / 864e5;
                    return d > 0 ? Math.round(d) : 0;
                },
                formato(v) { return '$ ' + Math.round(v).toLocaleString('es-CO'); },
            };
        }
    </script>
@endsection
