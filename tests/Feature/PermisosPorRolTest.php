<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** CP-39. Matriz rol x ruta: cada area solo entra a lo que le corresponde. */
class PermisosPorRolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // La matriz revisa permisos, no los recursos compilados de la interfaz.
        $this->withoutVite();
    }

    /**
     * @return array<string, array{string, string, int}>
     */
    public static function matriz(): array
    {
        $rutas = [
            'panel.reservas.index' => ['admin' => 200, 'recepcion' => 200, 'aseo' => 403, 'restaurante' => 403, 'mantenimiento' => 403],
            'panel.facturas.index' => ['admin' => 200, 'recepcion' => 200, 'aseo' => 403, 'restaurante' => 403, 'mantenimiento' => 403],
            'panel.aseo.index' => ['admin' => 200, 'recepcion' => 200, 'aseo' => 200, 'restaurante' => 403, 'mantenimiento' => 403],
            'panel.pedidos.index' => ['admin' => 200, 'recepcion' => 200, 'aseo' => 403, 'restaurante' => 200, 'mantenimiento' => 403],
            'panel.mantenimiento.index' => ['admin' => 200, 'recepcion' => 200, 'aseo' => 403, 'restaurante' => 403, 'mantenimiento' => 200],
            'panel.auditoria' => ['admin' => 200, 'recepcion' => 403, 'aseo' => 403, 'restaurante' => 403, 'mantenimiento' => 403],
        ];

        $casos = [];
        foreach ($rutas as $ruta => $roles) {
            foreach ($roles as $rol => $codigo) {
                $casos["{$rol} en {$ruta}"] = [$rol, $ruta, $codigo];
            }
        }

        return $casos;
    }

    #[DataProvider('matriz')]
    public function test_matriz_de_rol_y_ruta(string $rol, string $ruta, int $codigoEsperado): void
    {
        $usuario = User::factory()->rol($rol)->create();

        $this->actingAs($usuario)->get(route($ruta))->assertStatus($codigoEsperado);
    }
}
