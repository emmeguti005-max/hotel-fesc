<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Pedido;
use App\Models\Reserva;
use App\Models\TareaAseo;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Ciclo de la estadía: crea la reserva, el check-in ocupa la habitación, el check-out factura y manda a aseo. */
class Recepcion
{
    public function __construct(private Facturador $facturador) {}

    /**
     * Crea la reserva y, de paso, da de alta o actualiza al huésped por su documento.
     * Bloquea la habitación mientras verifica el choque de fechas para que dos
     * recepcionistas no puedan reservarla dos veces a la vez.
     *
     * @param  array<string, mixed>  $datosHuesped
     * @param  array<string, mixed>  $datosReserva
     */
    public function crear(Habitacion $habitacion, array $datosHuesped, array $datosReserva, ?int $usuarioId = null): Reserva
    {
        $personas = $datosReserva['adultos'] + ($datosReserva['ninos'] ?? 0);

        if ($personas > $habitacion->capacidad) {
            throw new DomainException("La habitación {$habitacion->numero} admite máximo {$habitacion->capacidad} personas.");
        }

        return DB::transaction(function () use ($habitacion, $datosHuesped, $datosReserva, $usuarioId) {
            $seCruza = Reserva::seCruzaCon($habitacion->id, $datosReserva['fecha_entrada'], $datosReserva['fecha_salida'])
                ->lockForUpdate()
                ->exists();

            if ($seCruza) {
                throw new DomainException("La habitación {$habitacion->numero} ya está reservada en esas fechas.");
            }

            $huesped = Huesped::updateOrCreate(
                ['documento' => $datosHuesped['documento']],
                array_filter($datosHuesped, fn ($v) => $v !== null) + ['pais' => 'Colombia'],
            );

            $reserva = Reserva::create([
                'huesped_id' => $huesped->id,
                'habitacion_id' => $habitacion->id,
                'fecha_entrada' => $datosReserva['fecha_entrada'],
                'fecha_salida' => $datosReserva['fecha_salida'],
                'adultos' => $datosReserva['adultos'],
                'ninos' => $datosReserva['ninos'] ?? 0,
                'tarifa_noche' => $habitacion->tarifa_noche,
                'observaciones' => $datosReserva['observaciones'] ?? null,
                'creada_por' => $usuarioId,
            ]);

            Auditoria::registrar('crear_reserva', 'reservas', $reserva->id, "{$reserva->fresh()->codigo} · {$huesped->nombre}");

            return $reserva;
        });
    }

    public function checkIn(Reserva $reserva, ?int $usuarioId = null): void
    {
        DB::transaction(function () use ($reserva) {
            $reserva = Reserva::whereKey($reserva->id)->lockForUpdate()->firstOrFail();
            $habitacion = Habitacion::whereKey($reserva->habitacion_id)->lockForUpdate()->firstOrFail();

            if ($reserva->estado !== Reserva::RESERVADA) {
                throw new DomainException('Solo se puede hacer check-in de una reserva en estado "reservada".');
            }

            if ($habitacion->estado !== Habitacion::DISPONIBLE) {
                throw new DomainException("La habitación {$habitacion->numero} no está disponible (estado: {$habitacion->estado}). Debe estar limpia y libre.");
            }

            $reserva->update(['estado' => Reserva::EN_CURSO, 'checkin_at' => now()]);
            $habitacion->update(['estado' => Habitacion::OCUPADA]);

            Auditoria::registrar('checkin', 'reservas', $reserva->id, "{$reserva->codigo} · Hab. {$habitacion->numero}");
        });
    }

    public function checkOut(Reserva $reserva, string $formaPago, ?int $usuarioId = null): Factura
    {
        return DB::transaction(function () use ($reserva, $formaPago, $usuarioId) {
            $reserva = Reserva::whereKey($reserva->id)->lockForUpdate()->firstOrFail();

            if ($reserva->estado !== Reserva::EN_CURSO) {
                throw new DomainException('Solo se puede hacer check-out de una estadía en curso.');
            }

            $pedidosAbiertos = $reserva->pedidos()->whereNotIn('estado', [Pedido::ENTREGADO, Pedido::CANCELADO])->count();
            if ($pedidosAbiertos > 0) {
                throw new DomainException("El huésped tiene {$pedidosAbiertos} pedido(s) del restaurante sin entregar. Entréguelos o cancélelos antes del check-out.");
            }

            $factura = $this->facturador->facturarEstadia($reserva, $formaPago, $usuarioId);

            $reserva->update(['estado' => Reserva::FINALIZADA, 'checkout_at' => now()]);
            $reserva->habitacion->update(['estado' => Habitacion::SUCIA]);

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
        if ($reserva->estado !== Reserva::RESERVADA) {
            throw new DomainException('Solo se pueden cancelar reservas que aún no hicieron check-in.');
        }

        $reserva->update(['estado' => Reserva::CANCELADA]);
        Auditoria::registrar('cancelar_reserva', 'reservas', $reserva->id, $reserva->codigo);
    }
}
