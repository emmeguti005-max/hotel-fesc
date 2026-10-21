<?php

namespace App\Support;

/**
 * Textos legibles y colores de badge para los enums del sistema, en un solo lugar
 * para que vistas, filtros y exportaciones digan lo mismo.
 */
class Etiquetas
{
    public const ROLES = [
        'admin' => 'Administración',
        'recepcion' => 'Recepción',
        'aseo' => 'Aseo / housekeeping',
        'restaurante' => 'Restaurante',
        'mantenimiento' => 'Mantenimiento',
    ];

    public const ESTADOS_HABITACION = [
        'disponible' => 'Disponible',
        'ocupada' => 'Ocupada',
        'sucia' => 'Sucia',
        'en_limpieza' => 'En limpieza',
        'mantenimiento' => 'Mantenimiento',
        'bloqueada' => 'Bloqueada',
    ];

    public const COLOR_HABITACION = [
        'disponible' => 'bg-green-100 text-green-800',
        'ocupada' => 'bg-blue-100 text-blue-800',
        'sucia' => 'bg-amber-100 text-amber-800',
        'en_limpieza' => 'bg-cyan-100 text-cyan-800',
        'mantenimiento' => 'bg-red-100 text-red-800',
        'bloqueada' => 'bg-stone-200 text-stone-700',
    ];

    public const TIPOS_HABITACION = [
        'sencilla' => 'Sencilla',
        'doble' => 'Doble',
        'suite' => 'Suite',
        'familiar' => 'Familiar',
    ];

    public const ESTADOS_RESERVA = [
        'reservada' => 'Reservada',
        'en_curso' => 'Hospedado',
        'finalizada' => 'Finalizada',
        'cancelada' => 'Cancelada',
    ];

    public const COLOR_RESERVA = [
        'reservada' => 'bg-amber-100 text-amber-800',
        'en_curso' => 'bg-blue-100 text-blue-800',
        'finalizada' => 'bg-green-100 text-green-800',
        'cancelada' => 'bg-stone-200 text-stone-600',
    ];

    public const TIPOS_ASEO = [
        'salida' => 'Salida (check-out)',
        'diaria' => 'Repaso diario',
        'profunda' => 'Limpieza profunda',
        'solicitud_huesped' => 'Pedido del huésped',
    ];

    public const ESTADOS_ASEO = [
        'pendiente' => 'Pendiente',
        'en_proceso' => 'En proceso',
        'terminada' => 'Terminada',
    ];

    public const COLOR_ASEO = [
        'pendiente' => 'bg-amber-100 text-amber-800',
        'en_proceso' => 'bg-cyan-100 text-cyan-800',
        'terminada' => 'bg-green-100 text-green-800',
    ];

    public const CATEGORIAS_PRODUCTO = [
        'desayuno' => 'Desayunos',
        'entrada' => 'Entradas',
        'plato_fuerte' => 'Platos fuertes',
        'bebida' => 'Bebidas',
        'postre' => 'Postres',
        'snack' => 'Snacks',
    ];

    public const ESTADOS_PEDIDO = [
        'recibido' => 'Recibido',
        'en_preparacion' => 'En preparación',
        'listo' => 'Listo para despacho',
        'despachado' => 'Despachado',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    public const COLOR_PEDIDO = [
        'recibido' => 'bg-amber-100 text-amber-800',
        'en_preparacion' => 'bg-orange-100 text-orange-800',
        'listo' => 'bg-cyan-100 text-cyan-800',
        'despachado' => 'bg-blue-100 text-blue-800',
        'entregado' => 'bg-green-100 text-green-800',
        'cancelado' => 'bg-stone-200 text-stone-600',
    ];

    public const ORIGENES_PEDIDO = [
        'habitacion' => 'Servicio a la habitación',
        'mesa' => 'Mesa del restaurante',
        'mostrador' => 'Para llevar / mostrador',
    ];

    public const FORMAS_PAGO = [
        'cargo_habitacion' => 'Cargo a la habitación',
        'efectivo' => 'Efectivo',
        'tarjeta' => 'Tarjeta',
        'transferencia' => 'Transferencia',
    ];

    public const TIPOS_SOLICITUD = [
        'electrico' => 'Eléctrico',
        'plomeria' => 'Plomería / agua',
        'aire_acondicionado' => 'Aire acondicionado',
        'tv_entretenimiento' => 'TV / entretenimiento',
        'wifi' => 'Wifi / internet',
        'cerrajeria' => 'Cerradura / llave',
        'mobiliario' => 'Mobiliario',
        'equipo_cocina' => 'Equipo de cocina',
        'otro' => 'Otro',
    ];

    public const ESTADOS_SOLICITUD = [
        'pendiente' => 'Pendiente',
        'en_proceso' => 'En proceso',
        'resuelto' => 'Resuelto',
        'no_resuelto' => 'No resuelto',
    ];

    public const COLOR_SOLICITUD = [
        'pendiente' => 'bg-amber-100 text-amber-800',
        'en_proceso' => 'bg-blue-100 text-blue-800',
        'resuelto' => 'bg-green-100 text-green-800',
        'no_resuelto' => 'bg-red-100 text-red-800',
    ];

    public const COLOR_PRIORIDAD = [
        'baja' => 'bg-stone-100 text-stone-600',
        'media' => 'bg-stone-100 text-stone-700',
        'alta' => 'bg-orange-100 text-orange-700',
        'urgente' => 'bg-red-100 text-red-700 font-semibold',
    ];

    public const TIPOS_EQUIPO = [
        'televisor' => 'Televisor',
        'aire_acondicionado' => 'Aire acondicionado',
        'minibar' => 'Minibar',
        'caja_fuerte' => 'Caja fuerte',
        'cerradura' => 'Cerradura electrónica',
        'telefono' => 'Teléfono',
        'computador' => 'Computador',
        'impresora' => 'Impresora',
        'datafono' => 'Datáfono',
        'red' => 'Red / wifi',
        'cocina' => 'Equipo de cocina',
        'lavanderia' => 'Lavandería',
        'piscina' => 'Piscina',
        'mobiliario' => 'Mobiliario',
        'otro' => 'Otro',
    ];

    public const TIPOS_UBICACION = [
        'lobby' => 'Lobby / recepción',
        'restaurante' => 'Restaurante',
        'cocina' => 'Cocina',
        'bodega' => 'Bodega',
        'oficina' => 'Oficina',
        'pasillo' => 'Pasillo',
        'recreacion' => 'Recreación',
        'servicio' => 'Servicio (lavandería, máquinas)',
        'otro' => 'Otro',
    ];

    public static function piso(int $numero): string
    {
        return $numero == -1 ? 'Sótano' : 'Piso '.$numero;
    }
}
