@php
    use App\Support\Etiquetas;
    $ubicacionActual = $equipo?->ubicacion;
    $initial = [
        'lugar' => old('habitacion_id', $equipo?->habitacion_id) ? 'habitacion' : ($ubicacionActual || old('ubicacion_id') ? 'zona' : 'habitacion'),
        'habitacion_id' => (string) old('habitacion_id', $equipo?->habitacion_id ?? ''),
        'torre_id' => $ubicacionActual?->area?->piso?->torre_id ?? '',
        'piso_id' => $ubicacionActual?->area?->piso_id ?? '',
        'area_id' => $ubicacionActual?->area_id ?? '',
        'ubicacion_id' => (string) old('ubicacion_id', $ubicacionActual?->id ?? ''),
    ];
@endphp

<div x-data="equipoForm({{ \Illuminate\Support\Js::from($torres) }}, {{ \Illuminate\Support\Js::from($initial) }})" class="space-y-5">
    <div class="flex gap-2">
        <label class="chip-retro cursor-pointer" :class="lugar === 'habitacion' ? 'bg-amber-300' : ''">
            <input type="radio" x-model="lugar" value="habitacion" class="sr-only"> En una habitación
        </label>
        <label class="chip-retro cursor-pointer" :class="lugar === 'zona' ? 'bg-amber-300' : ''">
            <input type="radio" x-model="lugar" value="zona" class="sr-only"> En una zona común
        </label>
    </div>

    <div x-show="lugar === 'habitacion'">
        <label class="block text-sm font-medium mb-1">Habitación</label>
        <select name="habitacion_id" x-model="habitacion_id" :disabled="lugar !== 'habitacion'" class="w-full rounded-md input-retro">
            <option value="">Selecciona...</option>
            @foreach ($habitaciones as $habitacion)
                <option value="{{ $habitacion->id }}">{{ $habitacion->numero }}</option>
            @endforeach
        </select>
    </div>

    <fieldset x-show="lugar === 'zona'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Torre</label>
            <select x-model="torre_id" @change="piso_id = area_id = ubicacion_id = ''" class="w-full rounded-md input-retro">
                <option value="">Selecciona...</option>
                <template x-for="torre in torres" :key="torre.id">
                    <option :value="torre.id" x-text="'Torre ' + torre.nombre"></option>
                </template>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Piso</label>
            <select x-model="piso_id" @change="area_id = ubicacion_id = ''" :disabled="!torre_id" class="w-full rounded-md input-retro disabled:bg-stone-50">
                <option value="">Selecciona...</option>
                <template x-for="piso in pisos" :key="piso.id">
                    <option :value="piso.id" x-text="piso.numero == -1 ? 'Sótano' : 'Piso ' + piso.numero"></option>
                </template>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Área</label>
            <select x-model="area_id" @change="ubicacion_id = ''" :disabled="!piso_id" class="w-full rounded-md input-retro disabled:bg-stone-50">
                <option value="">Selecciona...</option>
                <template x-for="area in areas" :key="area.id">
                    <option :value="area.id" x-text="area.nombre"></option>
                </template>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Zona</label>
            <select x-model="ubicacion_id" name="ubicacion_id" :disabled="lugar !== 'zona' || !area_id" class="w-full rounded-md input-retro disabled:bg-stone-50">
                <option value="">Selecciona...</option>
                <template x-for="ubicacion in ubicaciones" :key="ubicacion.id">
                    <option :value="ubicacion.id" x-text="ubicacion.nombre"></option>
                </template>
            </select>
        </div>
    </fieldset>

    <fieldset class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Tipo de activo</label>
            <select name="tipo" required class="w-full rounded-md input-retro">
                @foreach (Etiquetas::TIPOS_EQUIPO as $tipo => $texto)
                    <option value="{{ $tipo }}" @selected(old('tipo', $equipo?->tipo) === $tipo)>{{ $texto }}</option>
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
               placeholder="Ej: Televisor 43'' habitación A-201, Nevera industrial cocina"
               class="w-full rounded-md input-retro">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Responsable (opcional)</label>
        <input type="text" name="usuario_asignado" value="{{ old('usuario_asignado', $equipo?->usuario_asignado ?? '') }}" maxlength="100"
               placeholder="Ej: Chef ejecutivo, Recepción"
               class="w-full rounded-md input-retro">
    </div>

    <fieldset class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Código / placa</label>
            <input type="text" name="codigo" value="{{ old('codigo', $equipo?->codigo ?? '') }}" maxlength="50" class="w-full rounded-md input-retro">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Marca</label>
            <input type="text" name="marca" value="{{ old('marca', $equipo?->marca ?? '') }}" maxlength="100" class="w-full rounded-md input-retro">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Modelo</label>
            <input type="text" name="modelo" value="{{ old('modelo', $equipo?->modelo ?? '') }}" maxlength="100" class="w-full rounded-md input-retro">
        </div>
    </fieldset>

    <div>
        <label class="block text-sm font-medium mb-1">Número de serie</label>
        <input type="text" name="serie" value="{{ old('serie', $equipo?->serie ?? '') }}" maxlength="100" class="w-full rounded-md input-retro">
    </div>

    <fieldset class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Posición en el plano — X% (opcional)</label>
            <input type="number" name="pos_x" value="{{ old('pos_x', $equipo?->pos_x ?? '') }}" min="0" max="100" step="0.01" class="w-full rounded-md input-retro">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Posición en el plano — Y% (opcional)</label>
            <input type="number" name="pos_y" value="{{ old('pos_y', $equipo?->pos_y ?? '') }}" min="0" max="100" step="0.01" class="w-full rounded-md input-retro">
        </div>
        <p class="sm:col-span-2 text-xs text-stone-500">
            También puedes arrastrarlo directamente sobre el <a href="{{ route('plano.index') }}" target="_blank" class="text-black underline decoration-black/40 hover:decoration-black underline-offset-2 font-medium">plano</a>.
        </p>
    </fieldset>
</div>

<script>
    function equipoForm(torres, initial) {
        return {
            torres,
            lugar: initial.lugar,
            habitacion_id: initial.habitacion_id ?? '',
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
