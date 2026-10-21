<?php

namespace Tests\Unit;

use App\Models\Factura;
use App\Models\Huesped;
use App\Models\Reserva;
use App\Models\User;
use App\Services\Facturador;
use App\Services\Recepcion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Concerns\CreaHotel;
use Tests\TestCase;

/**
 * Prueba de unidad de Recepcion::checkOut aislada de Facturador mediante un
 * doble (mock de Mockery): lo que se verifica aqui es la logica propia de
 * Recepcion (reglas de estado, cambio de habitacion), no como Facturador
 * calcula la factura -- eso ya lo cubre FacturaTest.
 */
class RecepcionCheckOutUnitTest extends TestCase
{
    use CreaHotel, RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_checkout_delega_la_emision_de_la_factura_en_el_facturador(): void
    {
        $this->actingAs(User::factory()->create());
        $habitacion = $this->crearHabitacion();
        $reserva = Reserva::create([
            'huesped_id' => Huesped::create($this->datosHuesped())->id,
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => today()->subDay(),
            'fecha_salida' => today(),
            'adultos' => 1,
            'tarifa_noche' => $habitacion->tarifa_noche,
            'estado' => Reserva::EN_CURSO,
            'checkin_at' => now()->subDay(),
        ]);

        $facturaDoble = new Factura(['id' => 999, 'numero' => 'FV-TEST']);

        $facturadorDoble = Mockery::mock(Facturador::class);
        $facturadorDoble->shouldReceive('facturarEstadia')
            ->once()
            ->with(Mockery::on(fn (Reserva $r) => $r->is($reserva)), 'efectivo', null)
            ->andReturn($facturaDoble);

        $recepcion = new Recepcion($facturadorDoble);
        $resultado = $recepcion->checkOut($reserva, 'efectivo');

        $this->assertSame($facturaDoble, $resultado);
        $this->assertSame(Reserva::FINALIZADA, $reserva->refresh()->estado);
        $this->assertSame('sucia', $habitacion->refresh()->estado);
    }

    public function test_checkout_no_llama_al_facturador_si_la_reserva_no_esta_en_curso(): void
    {
        $habitacion = $this->crearHabitacion();
        $reserva = Reserva::create([
            'huesped_id' => Huesped::create($this->datosHuesped())->id,
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => today(),
            'fecha_salida' => today()->addDay(),
            'adultos' => 1,
            'tarifa_noche' => $habitacion->tarifa_noche,
        ]);

        $facturadorDoble = Mockery::mock(Facturador::class);
        $facturadorDoble->shouldNotReceive('facturarEstadia');

        $recepcion = new Recepcion($facturadorDoble);

        $this->expectException(\DomainException::class);
        $recepcion->checkOut($reserva, 'efectivo');
    }
}
