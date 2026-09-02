@extends('layouts.panel')

@section('title', 'Plano · '.($piso->numero == -1 ? 'Sótano' : 'Piso '.$piso->numero))

@php
    $cols = max(1, (int) ceil(sqrt(max(1, $piso->areas->count()))));
    $etiquetaPiso = $piso->numero == -1 ? 'Sótano — compartido por las 3 torres' : 'Torre '.$piso->torre->nombre.' — Piso '.$piso->numero;
@endphp

@section('content')
    <nav class="flex items-center mb-4">
        <a href="{{ route('plano.index') }}" class="tab-folder">Plano</a>
        <span class="tab-folder is-current">{{ $etiquetaPiso }}</span>
    </nav>
    <h2 class="text-2xl font-extrabold tracking-tight mb-4">
        {{ $etiquetaPiso }}
    </h2>

    @if ($piso->areas->isEmpty() && ! $piso->plano_imagen)
        <div class="card p-6 text-sm text-stone-400">
            Este piso todavía no tiene áreas registradas.
        </div>
    @else
        <div class="card p-4" @auth x-data="planoEditor()" @endauth>
            <div class="relative w-full aspect-[4/3] rounded-md overflow-hidden border border-stone-200 bg-stone-50"
                 @auth x-ref="canvas" @dragover.prevent @drop="onDrop($event)" @endauth>
                @if ($piso->plano_imagen)
                    <img src="{{ asset('storage/'.$piso->plano_imagen) }}" alt="Plano de {{ $piso->numero == -1 ? 'sótano' : 'piso '.$piso->numero }}"
                         class="absolute inset-0 w-full h-full object-contain">
                @else
                    <div class="absolute inset-0 grid gap-px bg-stone-200" style="grid-template-columns: repeat({{ $cols }}, minmax(0,1fr))">
                        @foreach ($piso->areas as $area)
                            <div class="bg-stone-50 flex items-start p-2">
                                <span class="text-xs font-medium text-stone-400">{{ $area->nombre }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @foreach ($posicionados as $equipo)
                    <a href="{{ route('equipos.ficha', $equipo) }}"
                       title="{{ $equipo->nombre }}{{ $equipo->usuario_asignado ? ' — '.$equipo->usuario_asignado : '' }}"
                       @auth draggable="true" @dragstart="onDragStart($event, '{{ route('panel.equipos.posicion', $equipo) }}')" @endauth
                       class="absolute -translate-x-1/2 -translate-y-1/2 flex items-center justify-center w-11 h-11 rounded-full bg-amber-300 border-2 border-black shadow-hard-sm hover:scale-110 transition-transform text-black @auth cursor-grab active:cursor-grabbing @endauth"
                       style="left: {{ $equipo->pos_x }}%; top: {{ $equipo->pos_y }}%;">
                        @include('equipos._icono', ['tipo' => $equipo->tipo, 'class' => 'w-6 h-6'])
                    </a>
                @endforeach
            </div>
            <p class="text-xs text-stone-500 mt-2">
                Toca un ícono para ver la ficha del equipo.
                @auth
                    Arrastra un equipo sobre el plano para ubicarlo o moverlo.
                @endauth
            </p>
        </div>
    @endif

    @if ($sinPosicion->isNotEmpty())
        <div class="card p-4 mt-6" @auth x-data="planoEditor()" @endauth>
            <h3 class="text-sm font-semibold text-stone-500 uppercase mb-3">Equipos sin ubicar en el plano</h3>
            <ul class="divide-y divide-stone-100 text-sm">
                @foreach ($sinPosicion as $equipo)
                    <li class="py-2 flex items-center justify-between"
                        @auth draggable="true" @dragstart="onDragStart($event, '{{ route('panel.equipos.posicion', $equipo) }}')" @endauth
                        @auth style="cursor: grab;" @endauth>
                        <span class="inline-flex items-center gap-2">
                            @include('equipos._icono', ['tipo' => $equipo->tipo, 'class' => 'w-4 h-4 text-stone-600'])
                            {{ $equipo->nombre }}
                            @if ($equipo->usuario_asignado)
                                <span class="text-stone-400">({{ $equipo->usuario_asignado }})</span>
                            @endif
                            <span class="text-stone-400">— {{ $equipo->ubicacion->nombre }}</span>
                        </span>
                        <a href="{{ route('equipos.ficha', $equipo) }}" draggable="false" class="chip-retro">Ver ficha <span class="arrow">→</span></a>
                    </li>
                @endforeach
            </ul>
            @auth
                <p class="text-xs text-stone-500 mt-3">Arrastra un equipo de esta lista hacia el plano de arriba para ubicarlo.</p>
            @endauth
        </div>
    @endif

    @auth
        <script>
            function planoEditor() {
                return {
                    onDragStart(e, url) {
                        e.dataTransfer.setData('text/plain', url);
                        e.dataTransfer.effectAllowed = 'move';
                    },
                    onDrop(e) {
                        const url = e.dataTransfer.getData('text/plain');
                        if (!url) {
                            return;
                        }

                        const rect = this.$refs.canvas.getBoundingClientRect();
                        const x = Math.min(100, Math.max(0, ((e.clientX - rect.left) / rect.width) * 100)).toFixed(2);
                        const y = Math.min(100, Math.max(0, ((e.clientY - rect.top) / rect.height) * 100)).toFixed(2);

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = url;
                        form.innerHTML = `
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="pos_x" value="${x}">
                            <input type="hidden" name="pos_y" value="${y}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    },
                };
            }
        </script>
    @endauth
@endsection
