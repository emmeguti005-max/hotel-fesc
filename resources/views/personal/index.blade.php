@extends('layouts.panel')

@section('title', 'Personal')

@php use App\Support\Etiquetas; @endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h2 class="text-2xl font-extrabold tracking-tight">Personal del hotel</h2>
        <a href="{{ route('panel.personal.create') }}" class="btn-retro px-4 py-2 rounded-md text-sm font-bold">+ Agregar persona</a>
    </div>

    <nav class="flex flex-wrap items-center mb-4">
        <a href="{{ route('panel.personal.index') }}" class="tab-folder {{ request('rol') ? '' : 'is-current' }}">Todos</a>
        @foreach (Etiquetas::ROLES as $rol => $texto)
            <a href="{{ route('panel.personal.index', ['rol' => $rol]) }}" class="tab-folder {{ request('rol') === $rol ? 'is-current' : '' }}">{{ $texto }}</a>
        @endforeach
    </nav>

    <div class="card overflow-hidden">
        <div class="file-titlebar">
            <span class="dot"></span><span class="dot"></span>
            <span class="name">personal.csv</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-stone-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Área / rol</th>
                        <th class="px-4 py-3">Cargo</th>
                        <th class="px-4 py-3">Correo</th>
                        <th class="px-4 py-3">Teléfono</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($usuarios as $usuario)
                        <tr class="hover:bg-stone-50 {{ $usuario->activo ? '' : 'text-stone-400' }}">
                            <td class="px-4 py-3 font-medium">{{ $usuario->nombre }}</td>
                            <td class="px-4 py-3">{{ Etiquetas::ROLES[$usuario->rol] ?? $usuario->rol }}</td>
                            <td class="px-4 py-3">{{ $usuario->cargo ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $usuario->correo }}</td>
                            <td class="px-4 py-3">{{ $usuario->telefono ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="badge-retro px-2 py-0.5 text-xs {{ $usuario->activo ? 'bg-green-100 text-green-800' : 'bg-stone-200 text-stone-600' }}">{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('panel.personal.edit', $usuario) }}" class="chip-retro">Editar</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
