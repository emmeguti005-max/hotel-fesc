<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Hotel completo de prueba: personal, torres/pisos/habitaciones/zonas comunes,
     * activos, menú y unas semanas de operación simulada (estadías, pedidos,
     * limpiezas, facturas y reportes de mantenimiento).
     */
    public function run(): void
    {
        $this->call([
            PersonalSeeder::class,
            EstructuraHotelSeeder::class,
            ActivosSeeder::class,
            MenuSeeder::class,
            OperacionDemoSeeder::class,
        ]);
    }
}
