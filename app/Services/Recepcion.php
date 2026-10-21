<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\TareaAseo;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Ciclo de la estadía: check-in ocupa la habitación; check-out la factura y la manda a aseo. */
class Recepcion
{
    public function __construct(private Facturador $facturador) {}

    public function checkIn(Reserva $reserva, ?int $usuarioId = null): void
    {
        DB::transaction(function () use ($reserva) {
            $reserva = Reserva::whereKey($reserva->id)->lockForUpdate()->firstOrFail();
            $habitacion = Habitacion::whereKey($reserva->habitacion_id)->lockForUpdate()->firstOrFail();

            if ($reserva->estado !== 'reservada') {
                throw new DomainException('Solo se puede hacer check-in de una reserva en estado "reservada".');
            }

            if ($habitacion->estado !== 'disponible') {
                throw new DomainException("La habitación {$habitacion->numero} no está disponible (estado: {$habitacion->estado}). Debe estar limpia y libre.");
            }

            $reserva->update(['estado' => 'en_curso', 'checkin_at' => now()]);
            $habitacion->update(['estado' => 'ocupada']);

            Auditoria::registrar('checkin', 'reservas', $reserva->id, "{$reserva->codigo} · Hab. {$habitacion->numero}");
        });
    }

    public function checkOut(Reserva $reserva, string $formaPago, ?int $usuarioId = null): Factura
    {
        return DB::transaction(function () use ($reserva, $formaPago, $usuarioId) {
            $reserva = Reserva::whereKey($reserva->id)->lockForUpdate()->firstOrFail();

            if ($reserva->estado !== 'en_curso') {
                throw new DomainException('Solo se puede hacer check-out de una estadía en curso.');
            }

            $pedidosAbiertos = $reserva->pedidos()->whereNotIn('estado', ['entregado', 'cancelado'])->count();
            if ($pedidosAbiertos > 0) {
                throw new DomainException("El huésped tiene {$pedidosAbiertos} pedido(s) del restaurante sin entregar. Entréguelos o cancélelos antes del check-out.");
            }

            $factura = $this->facturador->facturarEstadia($reserva, $formaPago, $usuarioId);

            $reserva->update(['estado' => 'finalizada', 'checkout_at' => now()]);
            $reserva->habitacion->update(['estado' => 'sucia']);

            TareaAseo::create([
                'habitacion_id' => $reserva->habitacion_id,
                'tipo' => 'salida',
                'prioridad' => 'alta',
                'notas' => "Salida de {$reserva->huesped->nombre} ({$reserva->codigo}).",
                'creada_por' => $usuarioId,
            ]);

            Auditoria::registrar('checkout', 'reservas', $reserva->id, "{$reserva->codigo} · {$factura->numero}");

            return $factura;
        });
    }

    public function cancelar(Reserva $reserva): void
    {
        if ($reserva->estado !== 'reservada') {
            throw new DomainException('Solo se pueden cancelar reservas que aún no hicieron check-in.');
        }

        $reserva->update(['estado' => 'cancelada']);
        Auditoria::registrar('cancelar_reserva', 'reservas', $reserva->id, $reserva->codigo);
    }
}
