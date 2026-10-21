<?php

namespace Tests\Feature;

use App\Models\Solicitud;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaEstructura;
use Tests\TestCase;

class SolicitudPublicaTest extends TestCase
{
    use CreaEstructura, RefreshDatabase;

    public function test_cualquiera_puede_reportar_un_problema_sin_cuenta(): void
    {
        $ubicacion = $this->crearUbicacion();

        $response = $this->post(route('solicitudes.store'), [
            'ubicacion_id' => $ubicacion->id,
            'tipo' => 'hardware',
            'descripcion' => 'La impresora no enciende.',
            'prioridad' => 'media',
        ]);

        $solicitud = Solicitud::firstOrFail();
        $response->assertRedirect(route('solicitudes.gracias', $solicitud));
        $this->assertSame('pendiente', $solicitud->estado);
        $this->assertNotEmpty($solicitud->token);
    }

    public function test_no_deja_elegir_un_equipo_que_no_pertenece_a_la_ubicacion_elegida(): void
    {
        $ubicacion = $this->crearUbicacion();
        $otraUbicacion = $this->crearUbicacion();
        $equipo = $this->crearEquipo($otraUbicacion);

        $response = $this->post(route('solicitudes.store'), [
            'ubicacion_id' => $ubicacion->id,
            'equipo_id' => $equipo->id,
            'tipo' => 'hardware',
            'descripcion' => 'Prueba.',
            'prioridad' => 'media',
        ]);

        $response->assertSessionHasErrors('equipo_id');
        $this->assertDatabaseCount('solicitudes', 0);
    }

    public function test_la_url_publica_usa_un_token_no_el_id_secuencial(): void
    {
        $ubicacion = $this->crearUbicacion();
        $solicitud = Solicitud::create([
            'ubicacion_id' => $ubicacion->id,
            'tipo' => 'hardware',
            'descripcion' => 'x',
            'prioridad' => 'media',
            'estado' => 'pendiente',
        ]);

        $this->get('/solicitudes/'.$solicitud->id.'/gracias')->assertNotFound();
        $this->get(route('solicitudes.gracias', $solicitud))->assertOk();
    }

    public function test_el_comprobante_no_esta_disponible_hasta_que_la_solicitud_se_cierra(): void
    {
        $ubicacion = $this->crearUbicacion();
        $solicitud = Solicitud::create([
            'ubicacion_id' => $ubicacion->id,
            'tipo' => 'hardware',
            'descripcion' => 'x',
            'prioridad' => 'media',
            'estado' => 'en_proceso',
        ]);

        $this->get(route('solicitudes.comprobante', $solicitud))
            ->assertRedirect(route('solicitudes.gracias', $solicitud));
    }
}
