@php
    $claseIcono = $class ?? 'w-4 h-4';
@endphp

@switch($tipo)
    @case('pc')
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

    @case('proyector')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="7" width="16" height="10" rx="2"/>
            <circle cx="10" cy="12" r="3"/>
            <path d="M18 10l4-2v8l-4-2z"/>
        </svg>
        @break

    @case('telefono')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>
        </svg>
        @break

    @case('red')
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="5" r="2"/>
            <circle cx="5" cy="19" r="2"/>
            <circle cx="19" cy="19" r="2"/>
            <path d="M12 7v6M12 13l-6 4M12 13l6 4"/>
        </svg>
        @break

    @default
        <svg class="{{ $claseIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.6 2.6-2-2z"/>
        </svg>
@endswitch
