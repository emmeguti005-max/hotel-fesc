@extends('layouts.panel')

@section('title', 'Habitaciones')

@php
    use App\Support\Etiquetas;
    $borde = [
        'disponible' => 'bg-green-100',
        'ocupada' => 'bg-blue-100',
        'sucia' => 'bg-amber-200',
        'en_limpieza' => 'bg-cyan-100',
        'mantenimiento' => 'bg-red-200',
        'bloqueada' => 'bg-stone-300',
    ];
@endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h2 class="text-2xl font-extrabold tracking-tight">Tablero de habitaciones</h2>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('panel.habitaciones.create') }}" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">+ Nueva habitación</a>
        @endif
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
        @foreach (Etiquetas::ESTADOS_HABITACION as $estado => $texto)
            <a href="{{ route('panel.habitaciones.index', array_merge(request()->except('estado'), request('estado') === $estado ? [] : ['estado' => $estado])) }}"
               class="badge-retro px-2.5 py-1 text-xs {{ Etiquetas::COLOR_HABITACION[$estado] }} {{ request('estado') === $estado ? 'ring-2 ring-black' : '' }}">
                {{ $texto }}: {{ $resumen->get($estado, 0) }}
            </a>
        @endforeach
    </div>

    <form method="GET" class="card p-3 mb-6 flex flex-wrap items-end gap-3">
        <input type="hidden" name="estado" value="{{ request('estado') }}">
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Torre</label>
            <select name="torre_id" onchange="this.form.submit()" class="rounded-md input-retro text-sm">
                <option value="">Todas</option>
                @foreach ($torres as $torre)
                    <option value="{{ $torre->id }}" @selected((string) request('torre_id') === (string) $torre->id)>Torre {{ $torre->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-stone-500 mb-1">Tipo</label>
            <select name="tipo" onchange="this.form.submit()" class="rounded-md input-retro text-sm">
                <option value="">Todos</option>
                @foreach (Etiquetas::TIPOS_HABITACION as $valor => $texto)
                    <option value="{{ $valor }}" @selected(request('tipo') === $valor)>{{ $texto }}</option>
                @endforeach
            </select>
        </div>
        @if (request()->anyFilled(['estado', 'tipo', 'torre_id']))
            <a href="{{ route('panel.habitaciones.index') }}" class="text-sm text-stone-500 hover:text-stone-700 pb-2">Limpiar filtros</a>
        @endif
    </form>

    @forelse ($grupos as $etiqueta => $habitaciones)
        <section class="mb-6">
            <span class="tab-folder is-current mb-2">{{ $etiqueta }}</span>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mt-2">
                @foreach ($habitaciones as $habitacion)
                    <a href="{{ route('panel.habitaciones.show', $habitacion) }}"
                       class="block border-2 border-black rounded-md p-3 shadow-hard-sm hover:shadow-none hover:translate-x-[3px] hover:translate-y-[3px] transition-all {{ $borde[$habitacion->estado] }}">
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-extrabold">{{ $habitacion->numero }}</span>
                            <span class="text-[10px] uppercase font-bold">{{ Etiquetas::TIPOS_HABITACION[$habitacion->tipo] }}</span>
                        </div>
                        <p class="text-xs font-semibold mt-1">{{ Etiquetas::ESTADOS_HABITACION[$habitacion->estado] }}</p>
                        <p class="text-[11px] text-stone-700 truncate mt-0.5">
                            @if ($habitacion->reservaActual)
                                {{ $habitacion->reservaActual->huesped->nombre }}
                            @else
                                {{ $habitacion->capacidad }} pers.
                            @endif
                        </p>
                    </a>
                @endforeach
            </div>
        </section>
    @empty
        <div class="card p-6 text-sm text-stone-400">No hay habitaciones con estos filtros.</div>
    @endforelse
@endsection
