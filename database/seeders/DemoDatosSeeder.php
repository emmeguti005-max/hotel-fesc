<?php

namespace Database\Seeders;

use App\Models\Piso;
use App\Models\Torre;
use Illuminate\Database\Seeder;

class DemoDatosSeeder extends Seeder
{
    /**
     * Datos de prueba (no reales) para poder probar el formulario mientras
     * se carga la información verdadera de la clínica.
     */
    public function run(): void
    {
        $piso = Piso::whereHas('torre', fn ($q) => $q->where('nombre', 'A'))
            ->where('numero', 1)
            ->firstOrFail();

        $area = $piso->areas()->create(['nombre' => 'Recepción (demo)']);

        $ubicacion = $area->ubicaciones()->create([
            'nombre' => 'Recepción principal',
            'tipo' => 'oficina',
        ]);

        $ubicacion->equipos()->create([
            'tipo' => 'pc',
            'nombre' => 'PC Recepción 01',
            'marca' => 'Dell',
        ]);

        $ubicacion->equipos()->create([
            'tipo' => 'impresora',
            'nombre' => 'Impresora Recepción',
            'marca' => 'HP',
        ]);
    }
}
