@extends('layouts.panel')

@section('title', 'Reservas')

@php
    use App\Support\Etiquetas;
    $vistas = [
        'activas' => 'Activas',
        'llegadas' => 'Llegadas pendientes',
        'hospedados' => 'Hospedados',
        'salidas' => 'Salidas de hoy',
        'historial' => 'Historial',
    ];
@endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Reservas y estadías</h2>
        <div class="flex items-center gap-3">
            <a href="{{ route('panel.huespedes.index') }}" class="chip-retro">Huéspedes</a>
            <a href="{{ route('panel.reservas.create') }}" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">+ Nueva reserva</a>
        </div>
    </div>

    <nav class="flex flex-wrap items-center mb-4">
        @foreach ($vistas as $clave => $texto)
            <a href="{{ route('panel.reservas.index', ['vista' => $clave] + request()->only('q')) }}" class="tab-folder {{ $vista === $clave ? 'is-current' : '' }}">{{ $texto }}</a>
        @endforeach
    </nav>

    <form method="GET" class="mb-4 flex gap-2">
        <input type="hidden" name="vista" value="{{ $vista }}">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por huésped, documento, habitación o código" class="flex-1 rounded-md input-retro text-sm">
        <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">Buscar</button>
    </form>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">reservas-{{ $vista }}.csv</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">Código</th>
                        <th class="px-4 py-3">Huésped</th>
                        <th class="px-4 py-3">Habitación</th>
                        <th class="px-4 py-3">Entrada</th>
                        <th class="px-4 py-3">Salida</th>
                        <th class="px-4 py-3">Personas</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($reservas as $reserva)
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-mono font-semibold">{{ $reserva->codigo }}</td>
                            <td class="px-4 py-3">
                                {{ $reserva->huesped->nombre }}
                                <span class="block text-xs text-stone-500">{{ $reserva->huesped->tipo_documento }} {{ $reserva->huesped->documento }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $reserva->habitacion->numero }}</td>
                            <td class="px-4 py-3">{{ $reserva->fecha_entrada->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 {{ $reserva->estado === 'en_curso' && $reserva->fecha_salida->lte(today()) ? 'text-red-700 font-semibold' : '' }}">{{ $reserva->fecha_salida->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $reserva->adultos + $reserva->ninos }}</td>
                            <td class="px-4 py-3"><span class="badge-retro px-2 py-0.5 text-xs {{ Etiquetas::COLOR_RESERVA[$reserva->estado] }}">{{ Etiquetas::ESTADOS_RESERVA[$reserva->estado] }}</span></td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('panel.reservas.show', $reserva) }}" class="chip-retro">Abrir <span class="arrow">→</span></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-stone-400">No hay reservas en esta vista.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $reservas->links() }}</div>
@endsection
