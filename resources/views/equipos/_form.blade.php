@php
    $ubicacionActual = $equipo?->ubicacion;
    $initial = [
        'torre_id' => $ubicacionActual?->area?->piso?->torre_id ?? '',
        'piso_id' => $ubicacionActual?->area?->piso_id ?? '',
        'area_id' => $ubicacionActual?->area_id ?? '',
        'ubicacion_id' => $ubicacionActual?->id ?? '',
    ];
    $tipos = ['pc', 'impresora', 'proyector', 'telefono', 'red', 'otro'];
@endphp

<div x-data="equipoForm({{ \Illuminate\Support\Js::from($torres) }}, {{ \Illuminate\Support\Js::from($initial) }})" class="space-y-5">
    <fieldset class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Torre</label>
            <select x-model="torre_id" @change="piso_id = area_id = ubicacion_id = ''"
                    class="w-full rounded-md input-retro">
                <option value="">Selecciona...</option>
                <template x-for="torre in torres" :key="torre.id">
                    <option :value="torre.id" x-text="'Torre ' + torre.nombre"></option>
                </template>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Piso</label>
            <select x-model="piso_id" @change="area_id = ubicacion_id = ''"
                    :disabled="!torre_id" class="w-full rounded-md input-retro disabled:bg-stone-50">
                <option value="">Selecciona...</option>
                <template x-for="piso in pisos" :key="piso.id">
                    <option :value="piso.id" x-text="piso.numero == -1 ? 'Sótano' : 'Piso ' + piso.numero"></option>
                </template>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Área</label>
            <select x-model="area_id" @change="ubicacion_id = ''"
                    :disabled="!piso_id" class="w-full rounded-md input-retro disabled:bg-stone-50">
                <option value="">Selecciona...</option>
                <template x-for="area in areas" :key="area.id">
                    <option :value="area.id" x-text="area.nombre"></option>
                </template>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Ubicación</label>
            <select x-model="ubicacion_id" name="ubicacion_id" :disabled="!area_id" required
                    class="w-full rounded-md input-retro disabled:bg-stone-50">
                <option value="">Selecciona...</option>
                <template x-for="ubicacion in ubicaciones" :key="ubicacion.id">
                    <option :value="ubicacion.id" x-text="ubicacion.nombre"></option>
                </template>
            </select>
            <p x-show="area_id && ubicaciones.length === 0" class="text-xs text-amber-600 mt-1">
                Esta área todavía no tiene ubicaciones cargadas.
            </p>
        </div>
    </fieldset>

    <fieldset class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Tipo de equipo</label>
            <select name="tipo" required class="w-full rounded-md input-retro">
                @foreach ($tipos as $tipo)
                    <option value="{{ $tipo }}" @selected(old('tipo', $equipo?->tipo) === $tipo)>{{ ucfirst($tipo) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Estado</label>
            <select name="estado" required class="w-full rounded-md input-retro">
                <option value="activo" @selected(old('estado', $equipo?->estado ?? 'activo') === 'activo')>Activo</option>
                <option value="de_baja" @selected(old('estado', $equipo?->estado ?? '') === 'de_baja')>De baja</option>
            </select>
        </div>
    </fieldset>

    <div>
        <label class="block text-sm font-medium mb-1">Nombre / descripción</label>
        <input type="text" name="nombre" value="{{ old('nombre', $equipo?->nombre ?? '') }}" required maxlength="100"
               placeholder="Ej: PC Recepción, Impresora HP-304"
               class="w-full rounded-md input-retro">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Usuario asignado (opcional)</label>
        <input type="text" name="usuario_asignado" value="{{ old('usuario_asignado', $equipo?->usuario_asignado ?? '') }}" maxlength="100"
               placeholder="Ej: Ana Rodríguez"
               class="w-full rounded-md input-retro">
        <p class="text-xs text-stone-500 mt-1">Quién usa normalmente este equipo — ayuda a identificarlo cuando quien reporta no sabe el nombre técnico.</p>
    </div>

    <fieldset class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Código / etiqueta</label>
            <input type="text" name="codigo" value="{{ old('codigo', $equipo?->codigo ?? '') }}" maxlength="50"
                   class="w-full rounded-md input-retro">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Marca</label>
            <input type="text" name="marca" value="{{ old('marca', $equipo?->marca ?? '') }}" maxlength="100"
                   class="w-full rounded-md input-retro">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Modelo</label>
            <input type="text" name="modelo" value="{{ old('modelo', $equipo?->modelo ?? '') }}" maxlength="100"
                   class="w-full rounded-md input-retro">
        </div>
    </fieldset>

    <div>
        <label class="block text-sm font-medium mb-1">Número de serie</label>
        <input type="text" name="serie" value="{{ old('serie', $equipo?->serie ?? '') }}" maxlength="100"
               class="w-full rounded-md input-retro">
    </div>

    <fieldset class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Posición en el plano — X% (opcional)</label>
            <input type="number" name="pos_x" value="{{ old('pos_x', $equipo?->pos_x ?? '') }}" min="0" max="100" step="0.01"
                   placeholder="Ej: 35.5"
                   class="w-full rounded-md input-retro">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Posición en el plano — Y% (opcional)</label>
            <input type="number" name="pos_y" value="{{ old('pos_y', $equipo?->pos_y ?? '') }}" min="0" max="100" step="0.01"
                   placeholder="Ej: 60.0"
                   class="w-full rounded-md input-retro">
        </div>
        <p class="sm:col-span-2 text-xs text-stone-500">
            Deja ambos vacíos si todavía no sabes dónde ubicarlo en el <a href="{{ route('plano.index') }}" target="_blank" class="text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium">plano</a>.
            0,0 es la esquina superior izquierda del piso; 100,100 la inferior derecha.
        </p>
    </fieldset>
</div>

<script>
    function equipoForm(torres, initial) {
        return {
            torres,
            torre_id: initial.torre_id ?? '',
            piso_id: initial.piso_id ?? '',
            area_id: initial.area_id ?? '',
            ubicacion_id: initial.ubicacion_id ?? '',
            get pisos() {
                return this.torres.find(t => t.id == this.torre_id)?.pisos ?? [];
            },
            get areas() {
                return this.pisos.find(p => p.id == this.piso_id)?.areas ?? [];
            },
            get ubicaciones() {
                return this.areas.find(a => a.id == this.area_id)?.ubicaciones ?? [];
            },
        };
    }
</script>
