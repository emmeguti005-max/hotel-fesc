<?php

namespace Tests\Feature;

use App\Models\Factura;
use App\Models\Huesped;
use App\Models\Pedido;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaHotel;
use Tests\TestCase;

class FacturaTest extends TestCase
{
    use CreaHotel, RefreshDatabase;

    private function crearFactura(): Factura
    {
        $habitacion = $this->crearHabitacion();
        $reserva = Reserva::create([
            'huesped_id' => Huesped::create($this->datosHuesped())->id,
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => today()->subDay(),
            'fecha_salida' => today(),
            'adultos' => 1,
            'tarifa_noche' => $habitacion->tarifa_noche,
        ]);

        return Factura::create([
            'tipo' => 'hospedaje',
            'reserva_id' => $reserva->id,
            'huesped_id' => $reserva->huesped_id,
            'cliente_nombre' => $reserva->huesped->nombre,
            'subtotal' => $habitacion->tarifa_noche,
            'iva' => round($habitacion->tarifa_noche * config('hotel.iva') / 100, 2),
            'total' => $habitacion->tarifa_noche * (1 + config('hotel.iva') / 100),
            'forma_pago' => 'efectivo',
        ]);
    }

    public function test_admin_puede_anular_una_factura_emitida(): void
    {
        $admin = User::factory()->admin()->create();
        $factura = $this->crearFactura();

        $response = $this->actingAs($admin)->post(route('panel.facturas.anular', $factura), [
            'motivo' => 'Cobro duplicado.',
        ]);

        $response->assertRedirect();
        $factura->refresh();
        $this->assertSame(Factura::ANULADA, $factura->estado);
        $this->assertSame('Cobro duplicado.', $factura->motivo_anulacion);
    }

    public function test_no_se_puede_anular_una_factura_ya_anulada(): void
    {
        $admin = User::factory()->admin()->create();
        $factura = $this->crearFactura();
        $factura->update(['estado' => Factura::ANULADA]);

        $response = $this->actingAs($admin)->post(route('panel.facturas.anular', $factura), [
            'motivo' => 'Otra vez.',
        ]);

        $response->assertSessionHasErrors('motivo');
    }

    public function test_anular_una_factura_libera_sus_pedidos_para_poder_refacturarlos(): void
    {
        $admin = User::factory()->admin()->create();
        $factura = $this->crearFactura();

        $pedido = Pedido::create([
            'origen' => 'habitacion',
            'habitacion_id' => $factura->reserva->habitacion_id,
            'reserva_id' => $factura->reserva_id,
            'estado' => Pedido::ENTREGADO,
            'forma_pago' => Pedido::CARGO_HABITACION,
            'total' => 15000,
            'factura_id' => $factura->id,
        ]);

        $this->actingAs($admin)->post(route('panel.facturas.anular', $factura), ['motivo' => 'Ajuste.']);

        $this->assertNull($pedido->refresh()->factura_id);
    }
}
