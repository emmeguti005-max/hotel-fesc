@extends('layouts.panel')

@section('title', 'Registro del personal de aseo')

@php use App\Support\Etiquetas; @endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Registro del personal de aseo</h2>
        <a href="{{ route('panel.aseo.index') }}" class="text-sm text-stone-500 hover:underline">← Tareas de aseo</a>
    </div>

    <form method="GET" class="card p-4 mb-6 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Desde</label>
            <input type="date" name="desde" value="{{ $desde->toDateString() }}" class="rounded-md input-retro text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Hasta</label>
            <input type="date" name="hasta" value="{{ $hasta->toDateString() }}" class="rounded-md input-retro text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Persona</label>
            <select name="usuario_id" class="rounded-md input-retro text-sm">
                <option value="">Todo el personal</option>
                @foreach ($personal as $p)
                    <option value="{{ $p->id }}" @selected((string) request('usuario_id') === (string) $p->id)>{{ $p->nombre }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">Filtrar</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach ($resumen as $fila)
            <div class="card p-4 {{ $fila->usuario->activo ? '' : 'opacity-60' }}">
                <p class="font-bold">{{ $fila->usuario->nombre }}</p>
                <p class="text-xs text-stone-500">{{ $fila->usuario->cargo ?? 'Aseo' }}{{ $fila->usuario->activo ? '' : ' · inactivo' }}</p>
                <p class="text-3xl font-extrabold mt-2">{{ $fila->total }} <span class="text-sm font-medium text-stone-500">limpiezas</span></p>
                <p class="text-xs text-stone-600 mt-1">Promedio: {{ $fila->promedio !== null ? $fila->promedio.' min' : '—' }} · Total: {{ round($fila->minutos / 60, 1) }} h</p>
                @if ($fila->por_tipo->isNotEmpty())
                    <p class="text-[11px] text-stone-500 mt-2">
                        {{ $fila->por_tipo->map(fn ($n, $tipo) => (Etiquetas::TIPOS_ASEO[$tipo] ?? $tipo).': '.$n)->implode(' · ') }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">registro-aseo.log</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Habitación</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Personal</th>
                        <th class="px-4 py-3">Inicio → fin</th>
                        <th class="px-4 py-3">Duración</th>
                        <th class="px-4 py-3">Novedades</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($terminadas as $tarea)
                        <tr>
                            <td class="px-4 py-2">{{ $tarea->fin_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 font-semibold">{{ $tarea->habitacion->numero }}</td>
                            <td class="px-4 py-2">{{ Etiquetas::TIPOS_ASEO[$tarea->tipo] }}</td>
                            <td class="px-4 py-2">{{ $tarea->asignadaA->nombre ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $tarea->inicio_at?->format('H:i') }} → {{ $tarea->fin_at->format('H:i') }}</td>
                            <td class="px-4 py-2">{{ $tarea->minutos() }} min</td>
                            <td class="px-4 py-2 text-stone-600">{{ $tarea->observaciones ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-stone-400">Sin limpiezas registradas en este periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
