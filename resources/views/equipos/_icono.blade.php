@php
    $claseIcono = $class ?? 'w-4 h-4';
@endphp

@switch($tipo)
    @case('televisor')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="5" width="20" height="13" rx="1.5"/>
            <path d="M8 21h8M9 2l3 3 3-3"/>
        </svg>
        @break

    @case('aire_acondicionado')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="4" width="20" height="8" rx="1.5"/>
            <path d="M6 9h12M7 15c0 2 2 2 2 4M12 15v5M17 15c0 2-2 2-2 4"/>
        </svg>
        @break

    @case('minibar')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="5" y="2" width="14" height="20" rx="1.5"/>
            <path d="M5 9h14M9 5v2M9 12v4"/>
        </svg>
        @break

    @case('caja_fuerte')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="16" rx="1.5"/>
            <circle cx="12" cy="12" r="3"/>
            <path d="M12 9v1M15 12h-1M6 20v1M18 20v1"/>
        </svg>
        @break

    @case('cerradura')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="5" y="11" width="14" height="10" rx="1.5"/>
            <path d="M8 11V7a4 4 0 0 1 8 0v4M12 15v2"/>
        </svg>
        @break

    @case('telefono')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>
        </svg>
        @break

    @case('computador')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="12" rx="1"/>
            <line x1="8" y1="20" x2="16" y2="20"/>
            <line x1="12" y1="16" x2="12" y2="20"/>
        </svg>
        @break

    @case('impresora')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 9V3h12v6"/>
            <rect x="3" y="9" width="18" height="8" rx="1"/>
            <path d="M6 21h12v-5H6z"/>
        </svg>
        @break

    @case('datafono')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="6" y="2" width="12" height="20" rx="2"/>
            <rect x="8.5" y="5" width="7" height="4" rx="0.5"/>
            <path d="M9 13h.01M12 13h.01M15 13h.01M9 16h.01M12 16h.01M15 16h.01"/>
        </svg>
        @break

    @case('red')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 9a15 15 0 0 1 20 0M5 12.5a10 10 0 0 1 14 0M8.5 16a5 5 0 0 1 7 0"/>
            <circle cx="12" cy="19.5" r="1"/>
        </svg>
        @break

    @case('cocina')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 13.9A4 4 0 0 1 7 6a5 5 0 0 1 10 0 4 4 0 0 1 1 7.9V20H6z"/>
            <path d="M6 17h12"/>
        </svg>
        @break

    @case('lavanderia')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="4" y="2" width="16" height="20" rx="1.5"/>
            <circle cx="12" cy="13" r="5"/>
            <path d="M7 5.5h.01M10 5.5h.01"/>
        </svg>
        @break

    @case('piscina')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 17c2 0 2-1.5 4-1.5s2 1.5 4 1.5 2-1.5 4-1.5 2 1.5 4 1.5 2-1.5 4-1.5M2 21c2 0 2-1.5 4-1.5s2 1.5 4 1.5 2-1.5 4-1.5 2 1.5 4 1.5 2-1.5 4-1.5"/>
            <path d="M8 14V5a2 2 0 0 1 4 0M16 14V5a2 2 0 0 0-4 0M8 9h8"/>
        </svg>
        @break

    @case('mobiliario')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 18v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6M3 15h18M5 10V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v4M4 18v2M20 18v2"/>
        </svg>
        @break

    @default
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.6 2.6-2-2z"/>
        </svg>
@endswitch
