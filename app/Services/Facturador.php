<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Factura;
use App\Models\Pedido;
use App\Models\Reserva;
use App\Support\Etiquetas;
use Illuminate\Support\Facades\DB;

class Facturador
{
    /**
     * Factura de la estadía: noches de hospedaje + todos los consumos del restaurante
     * cargados a la habitación que todavía no se habían facturado.
     */
    public function facturarEstadia(Reserva $reserva, string $formaPago, ?int $usuarioId = null): Factura
    {
        return DB::transaction(function () use ($reserva, $formaPago, $usuarioId) {
            $reserva->loadMissing(['huesped', 'habitacion']);
            $consumos = $reserva->consumosPendientes()->with('items')->lockForUpdate()->get();

            $items = [[
                'concepto' => sprintf(
                    'Hospedaje habitación %s (%s) · %s al %s',
                    $reserva->habitacion->numero,
                    Etiquetas::TIPOS_HABITACION[$reserva->habitacion->tipo] ?? $reserva->habitacion->tipo,
                    $reserva->fecha_entrada->format('d/m/Y'),
                    $reserva->fecha_salida->format('d/m/Y'),
                ),
                'cantidad' => $reserva->noches(),
                'precio_unitario' => (float) $reserva->tarifa_noche,
            ]];

            foreach ($consumos as $pedido) {
                foreach ($pedido->items as $item) {
                    $items[] = [
                        'concepto' => "Restaurante {$pedido->codigo} · {$item->descripcion}",
                        'cantidad' => $item->cantidad,
                        'precio_unitario' => (float) $item->precio_unitario,
                    ];
                }
            }

            $factura = $this->emitir([
                'tipo' => 'hospedaje',
                'reserva_id' => $reserva->id,
                'huesped_id' => $reserva->huesped_id,
                'cliente_nombre' => $reserva->huesped->nombre,
                'cliente_documento' => $reserva->huesped->tipo_documento.' '.$reserva->huesped->documento,
                'forma_pago' => $formaPago,
                'emitida_por' => $usuarioId,
            ], $items);

            Pedido::whereIn('id', $consumos->pluck('id'))->update(['factura_id' => $factura->id]);

            return $factura;
        });
    }

    /** Factura de un pedido del restaurante pagado en el momento (no cargado a habitación). */
    public function facturarPedido(Pedido $pedido, ?int $usuarioId = null): Factura
    {
        return DB::transaction(function () use ($pedido, $usuarioId) {
            $pedido->loadMissing(['items', 'reserva.huesped']);
            $huesped = $pedido->reserva?->huesped;

            $factura = $this->emitir([
                'tipo' => 'restaurante',
                'reserva_id' => $pedido->reserva_id,
                'huesped_id' => $huesped?->id,
                'cliente_nombre' => $pedido->cliente_nombre ?: ($huesped->nombre ?? 'Consumidor final'),
                'cliente_documento' => $huesped ? $huesped->tipo_documento.' '.$huesped->documento : null,
                'forma_pago' => $pedido->forma_pago,
                'emitida_por' => $usuarioId,
            ], $pedido->items->map(fn ($item) => [
                'concepto' => "{$pedido->codigo} · {$item->descripcion}",
                'cantidad' => $item->cantidad,
                'precio_unitario' => (float) $item->precio_unitario,
            ])->all());

            $pedido->update(['factura_id' => $factura->id]);

            return $factura;
        });
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  list<array{concepto: string, cantidad: int|float, precio_unitario: float}>  $items
     */
    private function emitir(array $datos, array $items): Factura
    {
        $subtotal = round(collect($items)->sum(fn ($i) => $i['cantidad'] * $i['precio_unitario']), 2);
        $iva = round($subtotal * config('hotel.iva') / 100, 2);

        $factura = Factura::create([
            ...$datos,
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $subtotal + $iva,
        ]);

        foreach ($items as $item) {
            $factura->items()->create([
                ...$item,
                'subtotal' => round($item['cantidad'] * $item['precio_unitario'], 2),
            ]);
        }

        Auditoria::registrar('emitir_factura', 'facturas', $factura->id, "{$factura->numero} · {$factura->cliente_nombre}");

        return $factura;
    }
}
