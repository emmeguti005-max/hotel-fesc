<?php

namespace Tests\Unit;

use App\Models\Huesped;
use App\Models\Pedido;
use App\Models\Reserva;
use App\Models\User;
use App\Services\Facturador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaHotel;
use Tests\TestCase;

/**
 * CP-35. Totales de la factura de la estadia con y sin consumos. El oraculo es
 * la cuenta a mano: noches x tarifa + consumos, y sobre eso el 19 % de IVA.
 */
class FacturadorTotalesTest extends TestCase
{
    use CreaHotel, RefreshDatabase;

    private Reserva $reserva;

    protected function setUp(): void
    {
        parent::setUp();

        config(['hotel.iva' => 19]);
        $this->actingAs(User::factory()->create());

        $habitacion = $this->crearHabitacion(['tarifa_noche' => 150000]);
        $this->reserva = Reserva::create([
            'huesped_id' => Huesped::create($this->datosHuesped())->id,
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => today()->subDays(2),
            'fecha_salida' => today(),
            'adultos' => 1,
            'tarifa_noche' => 150000,
            'estado' => Reserva::EN_CURSO,
        ]);
    }

    private function agregarConsumo(int $cantidad, float $precio, string $estado = Pedido::ENTREGADO): Pedido
    {
        $pedido = Pedido::create([
            'origen' => 'habitacion',
            'habitacion_id' => $this->reserva->habitacion_id,
            'reserva_id' => $this->reserva->id,
            'estado' => $estado,
            'forma_pago' => Pedido::CARGO_HABITACION,
            'total' => $cantidad * $precio,
        ]);
        $pedido->items()->create([
            'descripcion' => 'Almuerzo',
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => $cantidad * $precio,
        ]);

        return $pedido;
    }

    public function test_sin_consumos_factura_solo_el_hospedaje(): void
    {
        $factura = (new Facturador)->facturarEstadia($this->reserva, 'efectivo');

        $this->assertEquals(300000, (float) $factura->subtotal);
        $this->assertEquals(57000, (float) $factura->iva);
        $this->assertEquals(357000, (float) $factura->total);
        $this->assertCount(1, $factura->items);
    }

    public function test_con_consumos_suma_el_restaurante_y_marca_el_pedido_como_facturado(): void
    {
        $pedido = $this->agregarConsumo(2, 15000);

        $factura = (new Facturador)->facturarEstadia($this->reserva, 'efectivo');

        $this->assertEquals(330000, (float) $factura->subtotal);
        $this->assertEquals(62700, (float) $factura->iva);
        $this->assertEquals(392700, (float) $factura->total);
        $this->assertCount(2, $factura->items);
        $this->assertSame($factura->id, $pedido->refresh()->factura_id);
    }

    public function test_la_factura_y_la_reserva_reciben_su_consecutivo(): void
    {
        $factura = (new Facturador)->facturarEstadia($this->reserva, 'efectivo');

        $this->assertSame(sprintf('FV-%06d', $factura->id), $factura->refresh()->numero);
        $this->assertSame(sprintf('RES-%06d', $this->reserva->id), $this->reserva->refresh()->codigo);
    }

    public function test_un_pedido_sin_entregar_no_entra_en_la_factura(): void
    {
        $this->agregarConsumo(1, 15000, 'listo');

        $factura = (new Facturador)->facturarEstadia($this->reserva, 'efectivo');

        $this->assertEquals(300000, (float) $factura->subtotal);
    }
}
