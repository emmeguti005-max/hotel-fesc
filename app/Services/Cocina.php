<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Habitacion;
use App\Models\Pedido;
use App\Models\Producto;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Pedidos del restaurante: creación, avance por la cocina, despacho y entrega. */
class Cocina
{
    public function __construct(private Facturador $facturador) {}

    /**
     * @param  array<string, mixed>  $datos  origen, habitacion_id, mesa, cliente_nombre, forma_pago, notas
     * @param  array<int|string, int>  $cantidades  producto_id => cantidad
     */
    public function crearPedido(array $datos, array $cantidades, ?int $usuarioId = null): Pedido
    {
        $cantidades = array_filter(array_map('intval', $cantidades), fn ($c) => $c > 0);

        if (empty($cantidades)) {
            throw new DomainException('El pedido no tiene productos.');
        }

        return DB::transaction(function () use ($datos, $cantidades, $usuarioId) {
            $reservaId = null;

            if ($datos['origen'] === 'habitacion') {
                $habitacion = Habitacion::with('reservaActual')->findOrFail($datos['habitacion_id']);
                $reservaId = $habitacion->reservaActual?->id;

                if ($datos['forma_pago'] === 'cargo_habitacion' && ! $reservaId) {
                    throw new DomainException("La habitación {$habitacion->numero} no tiene un huésped hospedado: no se puede cargar a la habitación.");
                }
            } elseif ($datos['forma_pago'] === 'cargo_habitacion') {
                throw new DomainException('El cargo a la habitación sólo aplica a pedidos de servicio a la habitación.');
            }

            $productos = Producto::whereIn('id', array_keys($cantidades))->where('disponible', true)->get()->keyBy('id');

            if ($productos->count() !== count($cantidades)) {
                throw new DomainException('Algún producto del pedido ya no está disponible.');
            }

            $pedido = Pedido::create([
                'origen' => $datos['origen'],
                'habitacion_id' => $datos['origen'] === 'habitacion' ? $datos['habitacion_id'] : null,
                'reserva_id' => $reservaId,
                'mesa' => $datos['origen'] === 'mesa' ? ($datos['mesa'] ?? null) : null,
                'cliente_nombre' => $datos['cliente_nombre'] ?? null,
                'forma_pago' => $datos['forma_pago'],
                'notas' => $datos['notas'] ?? null,
                'estado' => 'recibido',
                'tomado_por' => $usuarioId,
            ]);

            $total = 0;
            foreach ($cantidades as $productoId => $cantidad) {
                $producto = $productos[$productoId];
                $subtotal = $cantidad * (float) $producto->precio;
                $total += $subtotal;

                $pedido->items()->create([
                    'producto_id' => $producto->id,
                    'descripcion' => $producto->nombre,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotal,
                ]);
            }

            $pedido->update(['total' => $total]);
            Auditoria::registrar('crear_pedido', 'pedidos', $pedido->id, $pedido->codigo.' · '.$pedido->destino());

            return $pedido;
        });
    }

    /**
     * Avanza el pedido al siguiente estado y registra quién hizo cada paso.
     * Al entregarse, si no va cargado a la habitación, se emite su factura.
     */
    public function avanzar(Pedido $pedido, ?int $usuarioId = null, ?string $recibidoPor = null): Pedido
    {
        return DB::transaction(function () use ($pedido, $usuarioId, $recibidoPor) {
            $pedido = Pedido::whereKey($pedido->id)->lockForUpdate()->firstOrFail();
            $siguiente = Pedido::SIGUIENTE[$pedido->estado] ?? null;

            if (! $siguiente) {
                throw new DomainException('Este pedido ya está cerrado.');
            }

            $cambios = ['estado' => $siguiente];
            match ($siguiente) {
                'en_preparacion' => $cambios += ['preparacion_at' => now(), 'preparado_por' => $usuarioId],
                'listo' => $cambios += ['listo_at' => now(), 'preparado_por' => $pedido->preparado_por ?? $usuarioId],
                'despachado' => $cambios += ['despachado_at' => now(), 'despachado_por' => $usuarioId],
                'entregado' => $cambios += ['entregado_at' => now(), 'recibido_por' => $recibidoPor],
            };

            $pedido->update($cambios);
            Auditoria::registrar('pedido_'.$siguiente, 'pedidos', $pedido->id, $pedido->codigo);

            if ($siguiente === 'entregado' && $pedido->forma_pago !== 'cargo_habitacion') {
                $this->facturador->facturarPedido($pedido, $usuarioId);
            }

            return $pedido->fresh();
        });
    }

    public function cancelar(Pedido $pedido): void
    {
        if (in_array($pedido->estado, ['despachado', 'entregado', 'cancelado'], true)) {
            throw new DomainException('Un pedido ya despachado o cerrado no se puede cancelar.');
        }

        $pedido->update(['estado' => 'cancelado']);
        Auditoria::registrar('cancelar_pedido', 'pedidos', $pedido->id, $pedido->codigo);
    }
}
