<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreaHotel;
use Tests\TestCase;

/**
 * CP-32 y CP-33. Valores limite de fechas y de cupo al crear una reserva.
 * Las fechas van como dias desde hoy para que la prueba no dependa del calendario.
 */
class ReservaLimitesTest extends TestCase
{
    use CreaHotel, RefreshDatabase;

    private User $recepcionista;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recepcionista = User::factory()->create();
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function reservar(array $datos): TestResponse
    {
        $habitacion = $this->crearHabitacion(['capacidad' => 2]);

        return $this->actingAs($this->recepcionista)->post(route('panel.reservas.store'), [
            'huesped' => $this->datosHuesped(),
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => today()->toDateString(),
            'fecha_salida' => today()->addDay()->toDateString(),
            'adultos' => 1,
            ...$datos,
        ]);
    }

    /**
     * @return array<string, array{int, int, string|null}>
     */
    public static function limitesDeFechas(): array
    {
        return [
            'una noche, el minimo valido' => [0, 1, null],
            'salida igual a la entrada' => [0, 0, 'fecha_salida'],
            'salida anterior a la entrada' => [2, 1, 'fecha_salida'],
            'entrada hoy, el limite valido' => [0, 3, null],
            'entrada ayer, ya vencida' => [-1, 1, 'fecha_entrada'],
        ];
    }

    #[DataProvider('limitesDeFechas')]
    public function test_limites_de_fechas(int $entrada, int $salida, ?string $campoConError): void
    {
        $respuesta = $this->reservar([
            'fecha_entrada' => today()->addDays($entrada)->toDateString(),
            'fecha_salida' => today()->addDays($salida)->toDateString(),
        ]);

        if ($campoConError) {
            $respuesta->assertSessionHasErrors($campoConError);
            $this->assertDatabaseCount('reservas', 0);
        } else {
            $respuesta->assertSessionHasNoErrors();
            $this->assertDatabaseCount('reservas', 1);
        }
    }

    /**
     * @return array<string, array{int, int, bool}>
     */
    public static function limitesDeCupo(): array
    {
        return [
            'capacidad exacta con adultos' => [2, 0, true],
            'capacidad exacta con un nino' => [1, 1, true],
            'capacidad + 1 con adultos' => [3, 0, false],
            'capacidad + 1 con un nino' => [2, 1, false],
        ];
    }

    #[DataProvider('limitesDeCupo')]
    public function test_limites_de_cupo(int $adultos, int $ninos, bool $seAcepta): void
    {
        $respuesta = $this->reservar(['adultos' => $adultos, 'ninos' => $ninos]);

        if ($seAcepta) {
            $respuesta->assertSessionHasNoErrors();
        } else {
            $respuesta->assertSessionHasErrors('operacion');
        }
        $this->assertDatabaseCount('reservas', $seAcepta ? 1 : 0);
    }
}
