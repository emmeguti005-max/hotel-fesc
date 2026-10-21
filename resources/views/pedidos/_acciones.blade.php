{{-- Botón para avanzar el pedido al siguiente paso de cocina (y cancelar mientras no salga). --}}
@php
    $etiquetaAvance = [
        'recibido' => 'Empezar a preparar',
        'en_preparacion' => 'Marcar listo',
        'listo' => 'Despachar (lo llevo yo)',
        'despachado' => 'Confirmar entrega',
    ];
@endphp

@if (isset($etiquetaAvance[$pedido->estado]))
    <form method="POST" action="{{ route('panel.pedidos.avanzar', $pedido) }}" class="flex items-center gap-1.5">
        @csrf
        @if ($pedido->estado === 'despachado')
            <input type="text" name="recibido_por" placeholder="¿Quién recibió?" maxlength="120"
                   class="rounded-md input-retro text-xs py-1 w-32">
        @endif
        <button type="submit" class="btn-retro px-3 py-1.5 rounded-md text-xs font-bold">{{ $etiquetaAvance[$pedido->estado] }}</button>
    </form>
@endif

@if (in_array($pedido->estado, ['recibido', 'en_preparacion', 'listo'], true))
    <form method="POST" action="{{ route('panel.pedidos.cancelar', $pedido) }}" onsubmit="return confirm('¿Cancelar el pedido {{ $pedido->codigo }}?')">
        @csrf
        <button type="submit" class="text-xs text-red-600 hover:underline px-1">Cancelar</button>
    </form>
@endif
