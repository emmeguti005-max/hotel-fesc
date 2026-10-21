<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Solicitud;
use App\Models\TareaAseo;
use App\Services\Cocina;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Lo que ve el huésped al escanear el QR de su habitación: pedir al restaurante
 * (cargado a la cuenta), pedir aseo o reportar un daño. Sin cuenta ni contraseña.
 */
class PortalController extends Controller
{
    public function inicio(): View
    {
        return view('portal.inicio');
    }

    public function habitacion(Habitacion $habitacion): View
    {
        $habitacion->load(['piso.torre', 'reservaActual.huesped']);
        $reserva = $habitacion->reservaActual;

        $productos = $reserva
            ? Producto::where('disponible', true)->orderBy('categoria')->orderBy('nombre')->get()->groupBy('categoria')
            : collect();

        $pedidos = $reserva ? $reserva->pedidos()->latest()->limit(10)->get() : collect();
        $aseoPendiente = $habitacion->tareasAseo()->where('estado', '!=', 'terminada')->exists();

        return view('portal.habitacion', compact('habitacion', 'reserva', 'productos', 'pedidos', 'aseoPendiente'));
    }

    public function pedir(Habitacion $habitacion, Request $request, Cocina $cocina): RedirectResponse
    {
        $datos = $request->validate([
            'cantidades' => ['required', 'array'],
            'cantidades.*' => ['nullable', 'integer', 'between:0,20'],
            'notas' => ['nullable', 'string', 'max:300'],
        ]);

        $pedido = $cocina->crearPedido([
            'origen' => 'habitacion',
            'habitacion_id' => $habitacion->id,
            'forma_pago' => 'cargo_habitacion',
            'notas' => $datos['notas'] ?? null,
        ], $datos['cantidades']);

        return redirect()->route('pedidos.publico', $pedido)->with('status', 'Tu pedido llegó a la cocina.');
    }

    public function solicitarAseo(Habitacion $habitacion, Request $request): RedirectResponse
    {
        $datos = $request->validate(['notas' => ['nullable', 'string', 'max:300']]);

        if (! $habitacion->reservaActual) {
            return back()->withErrors(['notas' => 'Esta habitación no tiene una estadía activa.']);
        }

        if ($habitacion->tareasAseo()->where('estado', '!=', 'terminada')->exists()) {
            return back()->with('status', 'Ya hay una limpieza pendiente para tu habitación. ¡En breve te atendemos!');
        }

        TareaAseo::create([
            'habitacion_id' => $habitacion->id,
            'tipo' => 'solicitud_huesped',
            'prioridad' => 'alta',
            'notas' => $datos['notas'] ?: 'El huésped pidió aseo desde el QR.',
        ]);

        return back()->with('status', 'Listo, el personal de aseo ya recibió tu solicitud.');
    }

    public function reportar(Habitacion $habitacion, Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'tipo' => ['required', Rule::in(['electrico', 'plomeria', 'aire_acondicionado', 'tv_entretenimiento', 'wifi', 'cerrajeria', 'mobiliario', 'otro'])],
            'descripcion' => ['required', 'string', 'max:1000'],
        ]);

        $solicitud = Solicitud::create($datos + [
            'habitacion_id' => $habitacion->id,
            'nombre_solicitante' => $habitacion->reservaActual?->huesped?->nombre,
            'prioridad' => $habitacion->reservaActual ? 'alta' : 'media',
            'estado' => 'pendiente',
            'ip_origen' => $request->ip(),
        ]);

        return redirect()->route('solicitudes.gracias', $solicitud);
    }

    /** Estado del pedido + registro de despacho (a esta URL lleva el QR del comprobante). */
    public function pedido(Pedido $pedido): View
    {
        $pedido->load(['items', 'habitacion.piso.torre', 'preparadoPor', 'despachadoPor', 'factura']);

        return view('pedidos.publico', compact('pedido'));
    }
}
