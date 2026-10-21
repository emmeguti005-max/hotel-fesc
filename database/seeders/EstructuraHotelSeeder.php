<?php

namespace Database\Seeders;

use App\Models\Piso;
use App\Models\Torre;
use Illuminate\Database\Seeder;

class EstructuraHotelSeeder extends Seeder
{
    /** Tarifa por noche antes de IVA, según tipo de habitación. */
    public const TARIFAS = [
        'sencilla' => 180000,
        'doble' => 260000,
        'familiar' => 340000,
        'suite' => 480000,
    ];

    public const CAPACIDAD = [
        'sencilla' => 1,
        'doble' => 2,
        'familiar' => 4,
        'suite' => 3,
    ];

    /**
     * Torre A: recepción, lobby y restaurante + habitaciones sencillas/dobles.
     * Torre B: zona húmeda y eventos + habitaciones dobles/familiares.
     * Torre C: lavandería y mantenimiento + suites.
     * Sótano (compartido): parqueadero, bodega y cuarto de máquinas.
     */
    public function run(): void
    {
        $torres = [
            'A' => [
                'pisos' => 5,
                'zonas' => [
                    'Recepción y lobby' => [['Recepción', 'lobby'], ['Lobby', 'lobby'], ['Oficina de gerencia', 'oficina']],
                    'Restaurante' => [['Restaurante Sabores del Norte', 'restaurante'], ['Cocina principal', 'cocina'], ['Bodega de alimentos', 'bodega']],
                ],
                'habitaciones' => 6,
                'tipos' => ['sencilla', 'doble', 'doble', 'sencilla', 'doble', 'doble'],
            ],
            'B' => [
                'pisos' => 5,
                'zonas' => [
                    'Zona húmeda' => [['Piscina', 'recreacion'], ['Gimnasio', 'recreacion'], ['Sauna y turco', 'recreacion']],
                    'Eventos' => [['Salón de eventos Guasimales', 'otro']],
                ],
                'habitaciones' => 6,
                'tipos' => ['doble', 'familiar', 'doble', 'doble', 'familiar', 'doble'],
            ],
            'C' => [
                'pisos' => 4,
                'zonas' => [
                    'Servicios' => [['Lavandería', 'servicio'], ['Cuarto de ropa blanca', 'bodega'], ['Taller de mantenimiento', 'oficina']],
                ],
                'habitaciones' => 4,
                'tipos' => ['suite', 'suite', 'suite', 'suite'],
            ],
        ];

        foreach ($torres as $nombre => $config) {
            $torre = Torre::create(['nombre' => $nombre]);

            foreach (range(1, $config['pisos']) as $numero) {
                $piso = $torre->pisos()->create(['numero' => $numero]);

                if ($numero === 1) {
                    foreach ($config['zonas'] as $area => $ubicaciones) {
                        $this->crearZona($piso, $area, $ubicaciones);
                    }

                    continue;
                }

                // Pisos de habitaciones: pasillo con su access point y las habitaciones en fila.
                $this->crearZona($piso, 'Circulación', [["Pasillo piso {$numero}", 'pasillo']]);

                foreach (range(1, $config['habitaciones']) as $i) {
                    $tipo = $config['tipos'][$i - 1];
                    $piso->habitaciones()->create([
                        'numero' => sprintf('%s-%d%02d', $nombre, $numero, $i),
                        'tipo' => $tipo,
                        'capacidad' => self::CAPACIDAD[$tipo],
                        'tarifa_noche' => self::TARIFAS[$tipo] + ($numero - 2) * 10000, // pisos altos, mejor vista
                        'descripcion' => match ($tipo) {
                            'sencilla' => 'Cama semidoble, escritorio, baño privado',
                            'doble' => 'Dos camas dobles o una king, baño privado',
                            'familiar' => 'Cama king + dos sencillas, sala pequeña',
                            'suite' => 'Cama king, sala, jacuzzi y vista panorámica',
                        },
                        'pos_x' => 8 + ($i - 1) * (84 / max(1, $config['habitaciones'] - 1)),
                        'pos_y' => 30,
                    ]);
                }
            }
        }

        // Sótano único, compartido por las tres torres (se cuelga de la torre A).
        $sotano = Torre::where('nombre', 'A')->first()->pisos()->create(['numero' => -1]);
        $this->crearZona($sotano, 'Sótano', [['Parqueadero', 'servicio'], ['Cuarto de máquinas', 'servicio'], ['Bodega general', 'bodega']]);
    }

    private function crearZona(Piso $piso, string $area, array $ubicaciones): void
    {
        $area = $piso->areas()->create(['nombre' => $area]);

        foreach ($ubicaciones as [$nombre, $tipo]) {
            $area->ubicaciones()->create(['nombre' => $nombre, 'tipo' => $tipo]);
        }
    }
}
