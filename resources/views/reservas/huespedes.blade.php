@extends('layouts.panel')

@section('title', 'Huéspedes')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Huéspedes</h2>
        <a href="{{ route('panel.reservas.index') }}" class="text-sm text-stone-500 hover:underline">← Reservas</a>
    </div>

    <form method="GET" class="mb-4 flex gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nombre o documento" class="flex-1 rounded-md input-retro text-sm">
        <button type="submit" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">Buscar</button>
    </form>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">huespedes.csv</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Documento</th>
                        <th class="px-4 py-3">Contacto</th>
                        <th class="px-4 py-3">Procedencia</th>
                        <th class="px-4 py-3">Estadías</th>
                        <th class="px-4 py-3">Ahora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($huespedes as $huesped)
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-medium">{{ $huesped->nombre }}</td>
                            <td class="px-4 py-3">{{ $huesped->tipo_documento }} {{ $huesped->documento }}</td>
                            <td class="px-4 py-3 text-stone-600">{{ $huesped->telefono ?? '—' }}<span class="block text-xs">{{ $huesped->correo }}</span></td>
                            <td class="px-4 py-3 text-stone-600">{{ $huesped->ciudad }}{{ $huesped->ciudad ? ', ' : '' }}{{ $huesped->pais }}</td>
                            <td class="px-4 py-3">{{ $huesped->reservas_count }}</td>
                            <td class="px-4 py-3">
                                @foreach ($huesped->reservas as $activa)
                                    <a href="{{ route('panel.reservas.show', $activa) }}" class="badge-retro px-2 py-0.5 text-xs bg-blue-100 text-blue-800">Hab. {{ $activa->habitacion->numero }}</a>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-stone-400">Sin huéspedes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $huespedes->links() }}</div>
@endsection
