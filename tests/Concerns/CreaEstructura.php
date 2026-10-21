<?php

namespace Tests\Concerns;

use App\Models\Area;
use App\Models\Equipo;
use App\Models\Piso;
use App\Models\Torre;
use App\Models\Ubicacion;

trait CreaEstructura
{
    protected function crearUbicacion(): Ubicacion
    {
        $torre = Torre::create(['nombre' => 'A']);
        $piso = $torre->pisos()->create(['numero' => 1]);
        $area = $piso->areas()->create(['nombre' => 'Recepción y lobby']);

        return $area->ubicaciones()->create(['nombre' => 'Recepción', 'tipo' => 'lobby']);
    }

    protected function crearEquipo(?Ubicacion $ubicacion = null): Equipo
    {
        $ubicacion ??= $this->crearUbicacion();

        return Equipo::create([
            'ubicacion_id' => $ubicacion->id,
            'tipo' => 'pc',
            'nombre' => 'PC Test',
            'estado' => 'activo',
        ]);
    }
}
