<?php

namespace Tests\Feature;

use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreaEstructura;
use Tests\TestCase;

class PanelSolicitudTest extends TestCase
{
    use CreaEstructura, RefreshDatabase;

    private function crearSolicitudPendiente(): Solicitud
    {
        return Solicitud::create([
            'ubicacion_id' => $this->crearUbicacion()->id,
            'tipo' => 'hardware',
            'descripcion' => 'No enciende.',
            'prioridad' => 'media',
            'estado' => 'pendiente',
        ]);
    }

    public function test_un_tecnico_puede_tomar_una_solicitud_pendiente(): void
    {
        $tecnico = User::factory()->create();
        $solicitud = $this->crearSolicitudPendiente();

        $this->actingAs($tecnico)->post(route('panel.tomar', $solicitud))
            ->assertRedirect();

        $solicitud->refresh();
        $this->assertSame('en_proceso', $solicitud->estado);
        $this->assertSame($tecnico->id, $solicitud->tecnico_id);
    }

    public function test_no_se_puede_tomar_dos_veces_la_misma_solicitud(): void
    {
        $primero = User::factory()->create();
        $segundo = User::factory()->create();
        $solicitud = $this->crearSolicitudPendiente();

        $this->actingAs($primero)->post(route('panel.tomar', $solicitud));
        $response = $this->actingAs($segundo)->post(route('panel.tomar', $solicitud));

        $response->assertSessionHasErrors('estado');
        $solicitud->refresh();
        $this->assertSame($primero->id, $solicitud->tecnico_id);
    }

    public function test_cerrar_una_solicitud_ya_cerrada_falla(): void
    {
        $tecnico = User::factory()->create();
        $solicitud = $this->crearSolicitudPendiente();
        $solicitud->update(['estado' => 'resuelto', 'fecha_cierre' => now()]);

        $response = $this->actingAs($tecnico)->post(route('panel.cerrar', $solicitud), [
            'estado' => 'resuelto',
        ]);

        $response->assertSessionHasErrors('estado');
    }

    public function test_un_tecnico_puede_adjuntar_una_foto_de_evidencia(): void
    {
        Storage::fake('public');
        $tecnico = User::factory()->create();
        $solicitud = $this->crearSolicitudPendiente();

        $response = $this->actingAs($tecnico)->post(route('panel.evidencias.store', $solicitud), [
            'foto' => UploadedFile::fake()->image('falla.jpg'),
        ]);

        $response->assertRedirect();
        $this->assertSame(1, $solicitud->evidencias()->count());
        Storage::disk('public')->assertExists($solicitud->evidencias()->first()->ruta_archivo);
    }

    public function test_rechaza_un_archivo_que_no_es_imagen_como_evidencia(): void
    {
        Storage::fake('public');
        $tecnico = User::factory()->create();
        $solicitud = $this->crearSolicitudPendiente();

        $response = $this->actingAs($tecnico)->post(route('panel.evidencias.store', $solicitud), [
            'foto' => UploadedFile::fake()->create('nota.pdf', 100),
        ]);

        $response->assertSessionHasErrors('foto');
        $this->assertSame(0, $solicitud->evidencias()->count());
    }
}
