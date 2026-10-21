<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreaEstructura;
use Tests\TestCase;

class AutenticacionTest extends TestCase
{
    use CreaEstructura, RefreshDatabase;

    public function test_un_visitante_sin_cuenta_no_puede_ver_el_plano_ni_la_ficha_de_equipo(): void
    {
        $equipo = $this->crearEquipo();

        $this->get(route('plano.index'))->assertRedirect(route('login'));
        $this->get(route('equipos.ficha', $equipo))->assertRedirect(route('login'));
    }

    public function test_login_con_credenciales_correctas_entra_al_panel(): void
    {
        $user = User::factory()->create(['correo' => 'tecnico@test.local', 'password' => Hash::make('secreto123')]);

        $response = $this->post(route('login.attempt'), [
            'correo' => 'tecnico@test.local',
            'password' => 'secreto123',
        ]);

        $response->assertRedirect(route('panel.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_con_credenciales_incorrectas_falla(): void
    {
        User::factory()->create(['correo' => 'tecnico@test.local']);

        $response = $this->post(route('login.attempt'), [
            'correo' => 'tecnico@test.local',
            'password' => 'incorrecta',
        ]);

        $response->assertSessionHasErrors('correo');
        $this->assertGuest();
    }

    public function test_una_cuenta_desactivada_no_puede_entrar(): void
    {
        User::factory()->inactivo()->create(['correo' => 'baja@test.local', 'password' => Hash::make('secreto123')]);

        $response = $this->post(route('login.attempt'), [
            'correo' => 'baja@test.local',
            'password' => 'secreto123',
        ]);

        $response->assertSessionHasErrors('correo');
        $this->assertGuest();
    }

    public function test_un_tecnico_no_admin_no_puede_entrar_a_estructura_ni_auditoria(): void
    {
        $tecnico = User::factory()->create();

        $this->actingAs($tecnico)->get(route('panel.estructura.torres'))->assertForbidden();
        $this->actingAs($tecnico)->get(route('panel.auditoria'))->assertForbidden();
    }

    public function test_un_admin_si_puede_entrar_a_estructura_y_auditoria(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('panel.estructura.torres'))->assertOk();
        $this->actingAs($admin)->get(route('panel.auditoria'))->assertOk();
    }
}
