<?php

namespace Database\Seeders;

use App\Models\Torre;
use Illuminate\Database\Seeder;

class EstructuraFisicaSeeder extends Seeder
{
    /**
     * Crea las 3 torres con sótano + 8 pisos cada una, sin áreas ni ubicaciones
     * todavía: eso se va cargando desde el panel admin conforme se levante la
     * info real de la clínica.
     */
    public function run(): void
    {
        foreach (['A', 'B', 'Excelencia'] as $nombreTorre) {
            $torre = Torre::create(['nombre' => $nombreTorre]);

            foreach ([-1, ...range(1, 8)] as $numero) {
                $torre->pisos()->create(['numero' => $numero]);
            }
        }
    }
}
