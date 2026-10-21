@extends('layouts.panel')

@section('title', $usuario ? 'Editar '.$usuario->nombre : 'Agregar persona')

@php use App\Support\Etiquetas; @endphp

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">{{ $usuario ? 'Editar '.$usuario->nombre : 'Agregar persona al personal' }}</h2>
        <a href="{{ route('panel.personal.index') }}" class="text-sm text-stone-500 hover:underline">← Personal</a>
    </div>

    <div class="card overflow-hidden max-w-2xl">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">{{ $usuario ? 'usuario-'.$usuario->id.'.edit' : 'usuario.nuevo' }}</span>
        </div>
        <form method="POST" action="{{ $usuario ? route('panel.personal.update', $usuario) : route('panel.personal.store') }}" class="p-6 space-y-4">
            @csrf
            @if ($usuario)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nombre completo</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $usuario?->nombre) }}" required maxlength="255" class="w-full rounded-md input-retro">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Correo (para iniciar sesión)</label>
                    <input type="email" name="correo" value="{{ old('correo', $usuario?->correo) }}" required maxlength="255" class="w-full rounded-md input-retro">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Área / rol en el sistema</label>
                    <select name="rol" required class="w-full rounded-md input-retro">
                        @foreach (Etiquetas::ROLES as $rol => $texto)
                            <option value="{{ $rol }}" @selected(old('rol', $usuario?->rol) === $rol)>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Cargo</label>
                    <input type="text" name="cargo" value="{{ old('cargo', $usuario?->cargo) }}" maxlength="100" placeholder="Ej: Camarera de pisos, Chef, Botones" class="w-full rounded-md input-retro">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $usuario?->telefono) }}" maxlength="30" class="w-full rounded-md input-retro">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ $usuario ? 'Nueva contraseña (opcional)' : 'Contraseña' }}</label>
                    <input type="password" name="password" {{ $usuario ? '' : 'required' }} minlength="8" autocomplete="new-password" class="w-full rounded-md input-retro">
                </div>
            </div>

            @if ($usuario)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="activo" value="1" @checked(old('activo', $usuario->activo))>
                    Cuenta activa (desmarcar si la persona ya no trabaja en el hotel)
                </label>
            @endif

            <div class="text-xs text-stone-500 border-2 border-dashed border-stone-300 rounded-md p-3">
                <p class="font-semibold mb-1">Qué ve cada rol</p>
                <p><strong>Recepción:</strong> tablero, reservas, check-in/out, facturas, asignar aseo, pedidos.</p>
                <p><strong>Aseo:</strong> sus tareas de limpieza (inicia y termina cada una).</p>
                <p><strong>Restaurante:</strong> cocina, despacho de pedidos y menú.</p>
                <p><strong>Mantenimiento:</strong> reportes de daños, activos y plano.</p>
                <p><strong>Administración:</strong> todo, más personal, estructura y auditoría.</p>
            </div>

            <button type="submit" class="btn-retro px-6 py-2 rounded-md font-bold">{{ $usuario ? 'Guardar cambios' : 'Agregar' }}</button>
        </form>
    </div>
@endsection
