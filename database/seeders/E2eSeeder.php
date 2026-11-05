<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Datos de las pruebas de Cypress: personal, habitaciones y menú, sin la operación
 * simulada. Así todas las habitaciones arrancan libres y el flujo es repetible.
 */
class E2eSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PersonalSeeder::class,
            EstructuraHotelSeeder::class,
            MenuSeeder::class,
        ]);
    }
}
