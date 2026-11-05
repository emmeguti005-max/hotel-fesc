<?php

namespace Tests\Feature;

use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Producto;
use App\Models\Reserva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaHotel;
use Tests\TestCase;

/** CP-37. El portal del QR es la unica parte sin login: solo se entra con el token. */
class PortalTokenTest extends TestCase
{
    use CreaHotel, RefreshDatabase;

    private Habitacion $habitacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->habitacion = $this->crearHabitacion(['numero' => '305']);
    }

    public function test_con_el_token_correcto_se_ve_la_habitacion(): void
    {
        $this->get(route('portal.habitacion', $this->habitacion->token))
            ->assertOk()
            ->assertSee('305');
    }

    public function test_un_token_inexistente_responde_404(): void
    {
        $this->get('/habitacion/token-que-no-existe')->assertNotFound();
    }

    public function test_el_id_secuencial_no_sirve_como_token(): void
    {
        $this->get('/habitacion/'.$this->habitacion->id)->assertNotFound();
    }

    public function test_un_token_invalido_no_puede_pedir_al_restaurante(): void
    {
        $this->post('/habitacion/token-que-no-existe/pedido', ['cantidades' => [1 => 1]])->assertNotFound();

        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_el_huesped_hospedado_puede_pedir_desde_el_qr(): void
    {
        // Quitar esta linea cuando se corrija el defecto.
        $this->markTestIncomplete('Defecto D-06: sin login, Auditoria::registrar falla porque auditoria.usuario_id no admite NULL.');

        $this->habitacion->update(['estado' => Habitacion::OCUPADA]);
        Reserva::create([
            'huesped_id' => Huesped::create($this->datosHuesped())->id,
            'habitacion_id' => $this->habitacion->id,
            'fecha_entrada' => today(),
            'fecha_salida' => today()->addDay(),
            'adultos' => 1,
            'tarifa_noche' => $this->habitacion->tarifa_noche,
            'estado' => Reserva::EN_CURSO,
            'checkin_at' => now(),
        ]);
        $producto = Producto::create(['nombre' => 'Desayuno', 'categoria' => 'desayuno', 'precio' => 20000, 'disponible' => true]);

        $this->post(route('portal.pedir', $this->habitacion->token), ['cantidades' => [$producto->id => 1]])
            ->assertRedirect();

        $this->assertDatabaseCount('pedidos', 1);
    }
}
