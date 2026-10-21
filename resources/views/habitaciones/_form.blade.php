@php use App\Support\Etiquetas; @endphp

<div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Piso</label>
            <select name="piso_id" required class="w-full rounded-md input-retro">
                <option value="">Selecciona...</option>
                @foreach ($pisos as $piso)
                    <option value="{{ $piso->id }}" @selected((int) old('piso_id', $habitacion?->piso_id) === $piso->id)>{{ $piso->etiqueta() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Número</label>
            <input type="text" name="numero" value="{{ old('numero', $habitacion?->numero) }}" required maxlength="20" placeholder="Ej: A-301" class="w-full rounded-md input-retro">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Tipo</label>
            <select name="tipo" required class="w-full rounded-md input-retro">
                @foreach (Etiquetas::TIPOS_HABITACION as $valor => $texto)
                    <option value="{{ $valor }}" @selected(old('tipo', $habitacion?->tipo) === $valor)>{{ $texto }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Capacidad (personas)</label>
            <input type="number" name="capacidad" value="{{ old('capacidad', $habitacion?->capacidad ?? 2) }}" min="1" max="10" required class="w-full rounded-md input-retro">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Tarifa por noche (COP, antes de IVA)</label>
            <input type="number" name="tarifa_noche" value="{{ old('tarifa_noche', $habitacion ? (int) $habitacion->tarifa_noche : '') }}" min="0" step="1000" required class="w-full rounded-md input-retro">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Descripción (opcional)</label>
        <input type="text" name="descripcion" value="{{ old('descripcion', $habitacion?->descripcion) }}" maxlength="255" placeholder="Ej: vista a la piscina, cama king" class="w-full rounded-md input-retro">
    </div>
</div>
