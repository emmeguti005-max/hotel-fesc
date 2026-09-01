@extends('layouts.app')

@section('title', 'Nueva solicitud')

@section('content')
    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">nueva-solicitud.form</span>
        </div>
        <div class="p-6">
        <h2 class="text-2xl font-extrabold tracking-tight mb-1">Reportar un problema</h2>
        <p class="text-sm text-stone-500 mb-6">No necesitas cuenta ni contraseña. Solo indícanos dónde y qué pasa.</p>

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-100 border-2 border-black text-red-900 text-sm p-3 font-medium shadow-hard-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('solicitudes.store') }}"
              x-data="solicitudForm({{ \Illuminate\Support\Js::from($torres) }})" class="space-y-5">
            @csrf

            <fieldset class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Torre</label>
                    <select x-model="torre_id" @change="piso_id = area_id = ubicacion_id = equipo_id = ''"
                            class="w-full rounded-md input-retro">
                        <option value="">Selecciona...</option>
                        <template x-for="torre in torres" :key="torre.id">
                            <option :value="torre.id" x-text="'Torre ' + torre.nombre"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Piso</label>
                    <select x-model="piso_id" @change="area_id = ubicacion_id = equipo_id = ''"
                            :disabled="!torre_id" class="w-full rounded-md input-retro disabled:bg-stone-50">
                        <option value="">Selecciona...</option>
                        <template x-for="piso in pisos" :key="piso.id">
                            <option :value="piso.id" x-text="piso.numero == -1 ? 'Sótano' : 'Piso ' + piso.numero"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Área</label>
                    <select x-model="area_id" @change="ubicacion_id = equipo_id = ''"
                            :disabled="!piso_id" class="w-full rounded-md input-retro disabled:bg-stone-50">
                        <option value="">Selecciona...</option>
                        <template x-for="area in areas" :key="area.id">
                            <option :value="area.id" x-text="area.nombre"></option>
                        </template>
                    </select>
                    <p x-show="piso_id && areas.length === 0" class="text-xs text-amber-600 mt-1">
                        Este piso todavía no tiene áreas cargadas.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Ubicación</label>
                    <select x-model="ubicacion_id" @change="equipo_id = ''" name="ubicacion_id"
                            :disabled="!area_id" required class="w-full rounded-md input-retro disabled:bg-stone-50">
                        <option value="">Selecciona...</option>
                        <template x-for="ubicacion in ubicaciones" :key="ubicacion.id">
                            <option :value="ubicacion.id" x-text="ubicacion.nombre"></option>
                        </template>
                    </select>
                </div>
            </fieldset>

            <div>
                <label class="block text-sm font-medium mb-1">Equipo afectado (opcional)</label>
                <select x-model="equipo_id" name="equipo_id" :disabled="!ubicacion_id"
                        class="w-full rounded-md input-retro disabled:bg-stone-50">
                    <option value="">Sin equipo específico (ej. instalación eléctrica, red)</option>
                    <template x-for="equipo in equipos" :key="equipo.id">
                        <option :value="equipo.id" x-text="(equipo.usuario_asignado ? equipo.usuario_asignado + ' — ' : '') + equipo.nombre + (equipo.marca ? ' (' + equipo.marca + ')' : '')"></option>
                    </template>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Tipo de problema</label>
                    <select name="tipo" required class="w-full rounded-md input-retro">
                        <option value="impresora" @selected(old('tipo') === 'impresora')>Impresora</option>
                        <option value="instalacion" @selected(old('tipo') === 'instalacion')>Instalación eléctrica</option>
                        <option value="red" @selected(old('tipo') === 'red')>Red / internet</option>
                        <option value="hardware" @selected(old('tipo') === 'hardware')>Hardware / equipo</option>
                        <option value="software" @selected(old('tipo') === 'software')>Software</option>
                        <option value="otro" @selected(old('tipo') === 'otro')>Otro</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Prioridad</label>
                    <select name="prioridad" required class="w-full rounded-md input-retro">
                        <option value="baja" @selected(old('prioridad') === 'baja')>Baja</option>
                        <option value="media" @selected(old('prioridad', 'media') === 'media')>Media</option>
                        <option value="alta" @selected(old('prioridad') === 'alta')>Alta</option>
                        <option value="urgente" @selected(old('prioridad') === 'urgente')>Urgente</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Describe el problema</label>
                <textarea name="descripcion" rows="4" required
                          class="w-full rounded-md input-retro">{{ old('descripcion') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">¿Quién reporta? (opcional)</label>
                <input type="text" name="nombre_solicitante" value="{{ old('nombre_solicitante') }}"
                       placeholder="Tu nombre, por si el técnico necesita preguntar algo"
                       class="w-full rounded-md input-retro">
            </div>

            <button type="submit" class="w-full sm:w-auto btn-retro px-6 py-2 rounded-md font-bold">
                Enviar solicitud
            </button>
        </form>
        </div>
    </div>

    <script>
        function solicitudForm(torres) {
            return {
                torres,
                torre_id: '',
                piso_id: '',
                area_id: '',
                ubicacion_id: '',
                equipo_id: '',
                get pisos() {
                    return this.torres.find(t => t.id == this.torre_id)?.pisos ?? [];
                },
                get areas() {
                    return this.pisos.find(p => p.id == this.piso_id)?.areas ?? [];
                },
                get ubicaciones() {
                    return this.areas.find(a => a.id == this.area_id)?.ubicaciones ?? [];
                },
                get equipos() {
                    return this.ubicaciones.find(u => u.id == this.ubicacion_id)?.equipos ?? [];
                },
            };
        }
    </script>
@endsection
