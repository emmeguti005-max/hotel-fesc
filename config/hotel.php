<?php

/*
 * Datos del hotel que aparecen en encabezados, facturas y registros de despacho.
 * Se cambian desde .env sin tocar código.
 */
return [
    'nombre' => env('HOTEL_NOMBRE', 'Hotel FESC'),
    'nit' => env('HOTEL_NIT', '900.123.456-7'),
    'direccion' => env('HOTEL_DIRECCION', 'Av. 4 # 15-30, Cúcuta, Norte de Santander'),
    'telefono' => env('HOTEL_TELEFONO', '(607) 555 0100'),
    'correo' => env('HOTEL_CORREO', 'reservas@hotelfesc.local'),

    // Porcentaje de IVA aplicado sobre el subtotal de cada factura.
    'iva' => (float) env('HOTEL_IVA', 19),

    // Hora estándar de salida, se muestra en la reserva.
    'hora_checkout' => env('HOTEL_HORA_CHECKOUT', '12:00'),
];
