@extends('layouts.panel')

@section('title', 'Auditoría')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Auditoría</h2>
        <span class="text-sm text-stone-500">{{ $registros->total() }} registros</span>
    </div>

    <form method="GET" class="card p-4 mb-6 grid grid-cols-2 sm:grid-cols-5 gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Usuario</label>
            <select name="usuario_id" onchange="this.form.submit()" class="w-full rounded-md input-retro text-sm">
                <option value="">Todos</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" @selected((string) request('usuario_id') === (string) $usuario->id)>{{ $usuario->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Acción</label>
            <select name="accion" onchange="this.form.submit()" class="w-full rounded-md input-retro text-sm">
                <option value="">Todas</option>
                @foreach ($acciones as $accion)
                    <option value="{{ $accion }}" @selected(request('accion') === $accion)>{{ ucfirst(str_replace('_', ' ', $accion)) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Desde</label>
            <input type="date" name="desde" value="{{ request('desde') }}" onchange="this.form.submit()"
                   class="w-full rounded-md input-retro text-sm">
        </div>

        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Hasta</label>
            <input type="date" name="hasta" value="{{ request('hasta') }}" onchange="this.form.submit()"
                   class="w-full rounded-md input-retro text-sm">
        </div>

        @if (request()->anyFilled(['usuario_id', 'accion', 'desde', 'hasta']))
            <a href="{{ route('panel.auditoria') }}" class="text-sm text-stone-500 hover:text-stone-700">Limpiar filtros</a>
        @endif
    </form>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">auditoria.log</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Usuario</th>
                    <th class="px-4 py-3">Acción</th>
                    <th class="px-4 py-3">Entidad</th>
                    <th class="px-4 py-3">Detalle</th>
                    <th class="px-4 py-3">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($registros as $registro)
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3 text-stone-500 whitespace-nowrap">{{ $registro->fecha->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $registro->usuario->nombre ?? '—' }}</td>
                        <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $registro->accion)) }}</td>
                        <td class="px-4 py-3 text-stone-600">{{ $registro->entidad }}{{ $registro->entidad_id ? ' #'.$registro->entidad_id : '' }}</td>
                        <td class="px-4 py-3 text-stone-600 max-w-xs truncate">{{ $registro->detalle ?? '—' }}</td>
                        <td class="px-4 py-3 text-stone-400">{{ $registro->ip ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-stone-400">No hay registros con estos filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $registros->links() }}
    </div>
@endsection
