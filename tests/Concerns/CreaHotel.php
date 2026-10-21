<?php

namespace Tests\Concerns;

use App\Models\Habitacion;
use App\Models\Torre;

trait CreaHotel
{
    /**
     * @param  array<string, mixed>  $atributos
     */
    protected function crearHabitacion(array $atributos = []): Habitacion
    {
        $torre = Torre::create(['nombre' => 'A-'.uniqid()]);
        $piso = $torre->pisos()->create(['numero' => 1]);

        return $piso->habitaciones()->create([
            'numero' => $atributos['numero'] ?? '101',
            'tipo' => $atributos['tipo'] ?? 'doble',
            'capacidad' => $atributos['capacidad'] ?? 2,
            'tarifa_noche' => $atributos['tarifa_noche'] ?? 150000,
            'estado' => $atributos['estado'] ?? Habitacion::DISPONIBLE,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function datosHuesped(array $overrides = []): array
    {
        return [
            'nombre' => 'Huesped de prueba',
            'tipo_documento' => 'CC',
            'documento' => '123456789',
            ...$overrides,
        ];
    }
}
