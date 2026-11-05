<?php

namespace Tests\Feature;

use App\Models\Habitacion;
use App\Models\TareaAseo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaHotel;
use Tests\TestCase;

/** CP-38. La habitacion sucia solo vuelve a estar disponible cuando termina la limpieza. */
class AseoFlujoTest extends TestCase
{
    use CreaHotel, RefreshDatabase;

    private User $camarera;

    private Habitacion $habitacion;

    private TareaAseo $tarea;

    protected function setUp(): void
    {
        parent::setUp();

        $this->camarera = User::factory()->rol('aseo')->create();
        $this->habitacion = $this->crearHabitacion(['estado' => Habitacion::SUCIA]);
        $this->tarea = TareaAseo::create([
            'habitacion_id' => $this->habitacion->id,
            'tipo' => 'salida',
            'prioridad' => 'alta',
        ]);
    }

    public function test_iniciar_y_terminar_deja_la_habitacion_disponible(): void
    {
        $this->actingAs($this->camarera)->post(route('panel.aseo.iniciar', $this->tarea))->assertSessionHasNoErrors();
        $this->assertSame('en_limpieza', $this->habitacion->refresh()->estado);

        $this->actingAs($this->camarera)->post(route('panel.aseo.terminar', $this->tarea))->assertSessionHasNoErrors();
        $this->assertSame(Habitacion::DISPONIBLE, $this->habitacion->refresh()->estado);
        $this->assertSame('terminada', $this->tarea->refresh()->estado);
    }

    public function test_solo_quien_inicio_la_limpieza_puede_terminarla(): void
    {
        $this->actingAs($this->camarera)->post(route('panel.aseo.iniciar', $this->tarea));

        $otra = User::factory()->rol('aseo')->create();
        $this->actingAs($otra)->post(route('panel.aseo.terminar', $this->tarea))->assertSessionHasErrors();

        $this->assertSame('en_limpieza', $this->habitacion->refresh()->estado);
    }
}
