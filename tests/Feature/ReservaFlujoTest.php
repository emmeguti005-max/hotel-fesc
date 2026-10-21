<?php

namespace Tests\Feature;

use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaHotel;
use Tests\TestCase;

class ReservaFlujoTest extends TestCase
{
    use CreaHotel, RefreshDatabase;

    public function test_recepcion_puede_crear_una_reserva(): void
    {
        $recepcionista = User::factory()->create();
        $habitacion = $this->crearHabitacion();

        $response = $this->actingAs($recepcionista)->post(route('panel.reservas.store'), [
            'huesped' => $this->datosHuesped(),
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => today()->toDateString(),
            'fecha_salida' => today()->addDays(2)->toDateString(),
            'adultos' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('reservas', 1);
        $this->assertDatabaseCount('huespedes', 1);
        $this->assertSame(Reserva::RESERVADA, Reserva::first()->estado);
    }

    public function test_no_se_puede_reservar_una_habitacion_ya_reservada_en_esas_fechas(): void
    {
        $recepcionista = User::factory()->create();
        $habitacion = $this->crearHabitacion();

        Reserva::create([
            'huesped_id' => Huesped::create($this->datosHuesped())->id,
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => today(),
            'fecha_salida' => today()->addDays(3),
            'adultos' => 1,
            'tarifa_noche' => $habitacion->tarifa_noche,
        ]);

        $response = $this->actingAs($recepcionista)->post(route('panel.reservas.store'), [
            'huesped' => $this->datosHuesped(['documento' => '987654321']),
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => today()->addDay()->toDateString(),
            'fecha_salida' => today()->addDays(4)->toDateString(),
            'adultos' => 1,
        ]);

        $response->assertSessionHasErrors('operacion');
        $this->assertDatabaseCount('reservas', 1);
    }

    public function test_no_se_puede_reservar_por_encima_de_la_capacidad(): void
    {
        $recepcionista = User::factory()->create();
        $habitacion = $this->crearHabitacion(['capacidad' => 2]);

        $response = $this->actingAs($recepcionista)->post(route('panel.reservas.store'), [
            'huesped' => $this->datosHuesped(),
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => today()->toDateString(),
            'fecha_salida' => today()->addDay()->toDateString(),
            'adultos' => 3,
        ]);

        $response->assertSessionHasErrors('operacion');
        $this->assertDatabaseCount('reservas', 0);
    }

    public function test_checkin_ocupa_la_habitacion(): void
    {
        $recepcionista = User::factory()->create();
        $habitacion = $this->crearHabitacion();
        $reserva = Reserva::create([
            'huesped_id' => Huesped::create($this->datosHuesped())->id,
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => today(),
            'fecha_salida' => today()->addDay(),
            'adultos' => 1,
            'tarifa_noche' => $habitacion->tarifa_noche,
        ]);

        $this->actingAs($recepcionista)->post(route('panel.reservas.checkin', $reserva))->assertRedirect();

        $this->assertSame(Reserva::EN_CURSO, $reserva->refresh()->estado);
        $this->assertSame(Habitacion::OCUPADA, $habitacion->refresh()->estado);
    }

    public function test_checkout_factura_la_estadia_y_manda_la_habitacion_a_aseo(): void
    {
        $recepcionista = User::factory()->create();
        $habitacion = $this->crearHabitacion(['estado' => Habitacion::OCUPADA]);
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

        $response = $this->actingAs($recepcionista)->post(route('panel.reservas.checkout', $reserva), [
            'forma_pago' => 'efectivo',
        ]);

        $response->assertRedirect();
        $reserva->refresh();
        $this->assertSame(Reserva::FINALIZADA, $reserva->estado);
        $this->assertSame(Habitacion::SUCIA, $habitacion->refresh()->estado);

        $factura = Factura::firstOrFail();
        $this->assertSame(Factura::EMITIDA, $factura->estado);
        $this->assertEquals(round($habitacion->tarifa_noche * config('hotel.iva') / 100, 2), (float) $factura->iva);
        $this->assertDatabaseHas('tareas_aseo', ['habitacion_id' => $habitacion->id, 'tipo' => 'salida']);
    }

    public function test_no_se_puede_hacer_checkout_de_una_reserva_que_no_esta_en_curso(): void
    {
        $recepcionista = User::factory()->create();
        $habitacion = $this->crearHabitacion();
        $reserva = Reserva::create([
            'huesped_id' => Huesped::create($this->datosHuesped())->id,
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => today(),
            'fecha_salida' => today()->addDay(),
            'adultos' => 1,
            'tarifa_noche' => $habitacion->tarifa_noche,
        ]);

        $response = $this->actingAs($recepcionista)->post(route('panel.reservas.checkout', $reserva), [
            'forma_pago' => 'efectivo',
        ]);

        $response->assertSessionHasErrors('operacion');
        $this->assertDatabaseCount('facturas', 0);
    }

    public function test_cancelar_una_reserva_reservada(): void
    {
        $recepcionista = User::factory()->create();
        $habitacion = $this->crearHabitacion();
        $reserva = Reserva::create([
            'huesped_id' => Huesped::create($this->datosHuesped())->id,
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => today(),
            'fecha_salida' => today()->addDay(),
            'adultos' => 1,
            'tarifa_noche' => $habitacion->tarifa_noche,
        ]);

        $this->actingAs($recepcionista)->post(route('panel.reservas.cancelar', $reserva))->assertRedirect();

        $this->assertSame(Reserva::CANCELADA, $reserva->refresh()->estado);
    }
}
