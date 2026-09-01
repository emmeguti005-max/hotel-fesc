@extends('layouts.app')

@section('title', 'Plano')
@section('container', 'max-w-5xl')

@section('content')
    <h2 class="text-2xl font-extrabold tracking-tight mb-1">Plano de la clínica</h2>
    <p class="text-sm text-stone-500 mb-6">Cada torre es un archivador; cada cajón, un piso. Abre uno para ver sus áreas y equipos.</p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        @forelse ($torres as $torre)
            <div class="cabinet">
                <div class="mb-3">
                    <span class="tab-folder is-current">Torre {{ $torre->nombre }}</span>
                </div>

                @forelse ($torre->pisos as $piso)
                    <a href="{{ route('plano.show', $piso) }}" class="drawer">
                        <span class="flex items-center gap-3">
                            <span class="handle"></span>
                            <span class="font-semibold text-sm">Piso {{ $piso->numero }}</span>
                        </span>
                        <span class="flex items-center gap-1.5 text-xs text-stone-500">
                            @if ($piso->plano_imagen)
                                <span title="Tiene plano real">📷</span>
                            @endif
                            {{ $piso->areas_count }} área{{ $piso->areas_count == 1 ? '' : 's' }}
                        </span>
                    </a>
                @empty
                    <p class="text-sm text-stone-500 px-1">Sin pisos.</p>
                @endforelse
            </div>
        @empty
            <p class="text-sm text-stone-400">Todavía no hay torres registradas.</p>
        @endforelse
    </div>

    @if ($sotano)
        <div class="cabinet mt-6">
            <div class="mb-3">
                <span class="tab-folder is-current">Sótano — compartido</span>
            </div>
            <a href="{{ route('plano.show', $sotano) }}" class="drawer">
                <span class="flex items-center gap-3">
                    <span class="handle"></span>
                    <span class="font-semibold text-sm">Nivel compartido por las 3 torres</span>
                </span>
                <span class="flex items-center gap-1.5 text-xs text-stone-500">
                    @if ($sotano->plano_imagen)
                        <span title="Tiene plano real">📷</span>
                    @endif
                    {{ $sotano->areas_count }} área{{ $sotano->areas_count == 1 ? '' : 's' }}
                </span>
            </a>
        </div>
    @endif
@endsection
