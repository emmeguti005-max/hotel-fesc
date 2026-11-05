<?php

namespace Tests\Feature;

use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\User;
use App\Services\Cocina;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaHotel;
use Tests\TestCase;

/** CP-36. Lo que el huesped carga a la habitacion debe llegar a la factura del check-out. */
class CargoHabitacionTest extends TestCase
{
    use CreaHotel, RefreshDatabase;

    private Reserva $reserva;

    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        config(['hotel.iva' => 19]);
        $this->actingAs(User::factory()->create());

        $habitacion = $this->crearHabitacion(['estado' => Habitacion::OCUPADA, 'tarifa_noche' => 100000]);
        $this->reserva = Reserva::create([
            'huesped_id' => Huesped::create($this->datosHuesped())->id,
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => today()->subDay(),
            'fecha_salida' => today()->addDay(),
            'adultos' => 1,
            'tarifa_noche' => 100000,
            'estado' => Reserva::EN_CURSO,
            'checkin_at' => now()->subDay(),
        ]);
        $this->producto = Producto::create([
            'nombre' => 'Desayuno',
            'categoria' => 'desayuno',
            'precio' => 20000,
            'disponible' => true,
        ]);
    }

    private function pedirACargo(): Pedido
    {
        return app(Cocina::class)->crearPedido([
            'origen' => 'habitacion',
            'habitacion_id' => $this->reserva->habitacion_id,
            'forma_pago' => Pedido::CARGO_HABITACION,
        ], [$this->producto->id => 2]);
    }

    public function test_el_cargo_a_la_habitacion_aparece_en_la_factura_del_checkout(): void
    {
        $pedido = $this->pedirACargo();
        $pedido->update(['estado' => Pedido::ENTREGADO]);

        $this->post(route('panel.reservas.checkout', $this->reserva), ['forma_pago' => 'efectivo'])
            ->assertSessionHasNoErrors();

        // 2 noches x 100.000 + 2 desayunos x 20.000 = 240.000; IVA 45.600
        $factura = Factura::firstOrFail();
        $this->assertEquals(240000, (float) $factura->subtotal);
        $this->assertEquals(45600, (float) $factura->iva);
        $this->assertSame($factura->id, $pedido->refresh()->factura_id);
    }

    public function test_no_deja_hacer_checkout_con_un_pedido_sin_entregar(): void
    {
        $this->pedirACargo();

        $this->post(route('panel.reservas.checkout', $this->reserva), ['forma_pago' => 'efectivo'])
            ->assertSessionHasErrors('operacion');

        $this->assertDatabaseCount('facturas', 0);
        $this->assertSame(Reserva::EN_CURSO, $this->reserva->refresh()->estado);
    }
}
