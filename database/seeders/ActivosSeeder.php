<?php

namespace Database\Seeders;

use App\Models\Equipo;
use App\Models\Habitacion;
use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

class ActivosSeeder extends Seeder
{
    /**
     * Dotación estándar de cada habitación y equipos de las zonas comunes.
     * Cada activo queda con código, marca, modelo y serie, y ubicado en el plano.
     */
    public function run(): void
    {
        $dotacion = [
            // [tipo, prefijo código, nombre, marca, modelo, desplazamiento Y en el plano]
            ['televisor', 'TV', 'Televisor 43"', 'Samsung', 'UN43T5300', 12],
            ['aire_acondicionado', 'AC', 'Aire acondicionado', 'LG', 'Dual Inverter 12000 BTU', 22],
            ['minibar', 'MB', 'Minibar', 'Haceb', 'Frigobar 50 L', 32],
            ['caja_fuerte', 'CF', 'Caja fuerte', 'Yale', 'YSV/200', 42],
            ['cerradura', 'CE', 'Cerradura electrónica', 'Onity', 'HT24', 52],
        ];

        foreach (Habitacion::orderBy('numero')->get() as $habitacion) {
            foreach ($dotacion as [$tipo, $prefijo, $nombre, $marca, $modelo, $dy]) {
                $marcaFinal = $habitacion->tipo === 'suite' && $tipo === 'televisor' ? 'LG' : $marca;
                $modeloFinal = $habitacion->tipo === 'suite' && $tipo === 'televisor' ? 'OLED 55" C3' : $modelo;

                Equipo::create([
                    'habitacion_id' => $habitacion->id,
                    'tipo' => $tipo,
                    'codigo' => $prefijo.'-'.str_replace('-', '', $habitacion->numero),
                    'nombre' => "{$nombre} · {$habitacion->numero}",
                    'marca' => $marcaFinal,
                    'modelo' => $modeloFinal,
                    'serie' => strtoupper(substr(md5($prefijo.$habitacion->numero), 0, 10)),
                    'pos_x' => $habitacion->pos_x,
                    'pos_y' => $habitacion->pos_y + $dy,
                ]);
            }
        }

        $zonas = [
            'Recepción' => [
                ['computador', 'PC-REC-01', 'Computador recepción 1', 'Lenovo', 'ThinkCentre M70q', 'Recepción'],
                ['computador', 'PC-REC-02', 'Computador recepción 2', 'Lenovo', 'ThinkCentre M70q', 'Recepción'],
                ['impresora', 'IMP-REC-01', 'Impresora de facturas', 'Epson', 'TM-T20III', 'Recepción'],
                ['datafono', 'DAT-REC-01', 'Datáfono recepción', 'Redeban', 'Ingenico Move/5000', 'Recepción'],
                ['telefono', 'TEL-REC-01', 'Conmutador / teléfono recepción', 'Grandstream', 'GXP2170', 'Recepción'],
            ],
            'Lobby' => [
                ['red', 'AP-LOB-01', 'Access point lobby', 'Ubiquiti', 'UniFi U6 Pro', null],
                ['televisor', 'TV-LOB-01', 'Pantalla informativa lobby', 'Samsung', 'QM55B', null],
                ['mobiliario', 'MOB-LOB-01', 'Juego de sala lobby', 'Tugó', 'Sofá 3 puestos + 2 poltronas', null],
            ],
            'Oficina de gerencia' => [
                ['computador', 'PC-GER-01', 'Portátil gerencia', 'Dell', 'Latitude 5440', 'Gerencia'],
                ['impresora', 'IMP-GER-01', 'Multifuncional gerencia', 'HP', 'LaserJet Pro MFP 4103', 'Gerencia'],
            ],
            'Restaurante Sabores del Norte' => [
                ['computador', 'POS-RES-01', 'Caja / POS restaurante', 'HP', 'Engage One Prime', 'Restaurante'],
                ['datafono', 'DAT-RES-01', 'Datáfono restaurante', 'Redeban', 'Ingenico Move/5000', 'Restaurante'],
                ['red', 'AP-RES-01', 'Access point restaurante', 'Ubiquiti', 'UniFi U6 Lite', null],
                ['aire_acondicionado', 'AC-RES-01', 'Aire central restaurante', 'Carrier', 'Piso-techo 36000 BTU', null],
            ],
            'Cocina principal' => [
                ['cocina', 'COC-01', 'Estufa industrial 6 puestos', 'Indufrial', 'EI-6P', 'Chef ejecutivo'],
                ['cocina', 'COC-02', 'Horno combi', 'Rational', 'iCombi Pro 6-1/1', 'Chef ejecutivo'],
                ['cocina', 'COC-03', 'Nevera industrial 2 puertas', 'Imbera', 'VR-35', 'Chef ejecutivo'],
                ['cocina', 'COC-04', 'Congelador horizontal', 'Haceb', 'Congelador 300 L', 'Chef ejecutivo'],
                ['cocina', 'COC-05', 'Campana extractora', 'Indufrial', 'Campana 2,5 m', 'Chef ejecutivo'],
                ['cocina', 'COC-06', 'Lavavajillas industrial', 'Hobart', 'AM15', 'Chef ejecutivo'],
                ['impresora', 'IMP-COC-01', 'Impresora de comandas', 'Epson', 'TM-U220', 'Cocina'],
            ],
            'Piscina' => [
                ['piscina', 'PIS-01', 'Motobomba de piscina', 'Pentair', 'SuperFlo VS', 'Mantenimiento'],
                ['piscina', 'PIS-02', 'Filtro de arena', 'Hayward', 'Pro Series S244T', 'Mantenimiento'],
                ['piscina', 'PIS-03', 'Calentador / bomba de calor', 'AstralPool', 'Heat Line+ 20', 'Mantenimiento'],
            ],
            'Gimnasio' => [
                ['otro', 'GYM-01', 'Caminadora', 'Life Fitness', 'Integrity', null],
                ['otro', 'GYM-02', 'Bicicleta estática', 'Life Fitness', 'IC5', null],
                ['televisor', 'TV-GYM-01', 'Televisor gimnasio', 'LG', '50UR8750', null],
            ],
            'Sauna y turco' => [
                ['otro', 'SPA-01', 'Generador de vapor (turco)', 'Tylö', 'VA 12', 'Mantenimiento'],
            ],
            'Salón de eventos Guasimales' => [
                ['otro', 'EVT-01', 'Videobeam salón', 'Epson', 'PowerLite L265F', null],
                ['otro', 'EVT-02', 'Sonido salón', 'Yamaha', 'STAGEPAS 600BT', null],
                ['aire_acondicionado', 'AC-EVT-01', 'Aire central salón', 'Carrier', 'Piso-techo 48000 BTU', null],
            ],
            'Lavandería' => [
                ['lavanderia', 'LAV-01', 'Lavadora industrial 1', 'Speed Queen', 'SC40', 'Ama de llaves'],
                ['lavanderia', 'LAV-02', 'Lavadora industrial 2', 'Speed Queen', 'SC40', 'Ama de llaves'],
                ['lavanderia', 'LAV-03', 'Secadora industrial 1', 'Speed Queen', 'ST030', 'Ama de llaves'],
                ['lavanderia', 'LAV-04', 'Secadora industrial 2', 'Speed Queen', 'ST030', 'Ama de llaves'],
                ['lavanderia', 'LAV-05', 'Planchadora de rodillo', 'Girbau', 'PB32', 'Ama de llaves'],
            ],
            'Taller de mantenimiento' => [
                ['computador', 'PC-MAN-01', 'Computador mantenimiento', 'Lenovo', 'V50s', 'Jefe de mantenimiento'],
            ],
            'Cuarto de máquinas' => [
                ['otro', 'MAQ-01', 'Planta eléctrica', 'Cummins', 'C150 D6', 'Mantenimiento'],
                ['otro', 'MAQ-02', 'Hidroflo / bomba de agua', 'Barnes', 'Hidroflo 3 HP', 'Mantenimiento'],
                ['red', 'RED-CORE-01', 'Switch principal', 'Cisco', 'CBS350-24P', 'Mantenimiento'],
                ['otro', 'MAQ-03', 'UPS rack de comunicaciones', 'APC', 'Smart-UPS 3000', 'Mantenimiento'],
            ],
            'Parqueadero' => [
                ['otro', 'PAR-01', 'Talanquera de acceso', 'FAAC', '620 Standard', null],
            ],
        ];

        $porPiso = []; // cuántos activos van en cada piso, para repartirlos en cuadrícula sin encimarse

        foreach ($zonas as $zona => $activos) {
            $ubicacion = Ubicacion::with('area')->where('nombre', $zona)->firstOrFail();

            foreach ($activos as [$tipo, $codigo, $nombre, $marca, $modelo, $responsable]) {
                $i = $porPiso[$ubicacion->area->piso_id] = ($porPiso[$ubicacion->area->piso_id] ?? -1) + 1;
                Equipo::create([
                    'ubicacion_id' => $ubicacion->id,
                    'tipo' => $tipo,
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'usuario_asignado' => $responsable,
                    'marca' => $marca,
                    'modelo' => $modelo,
                    'serie' => strtoupper(substr(md5($codigo), 0, 10)),
                    'pos_x' => 8 + ($i % 7) * 14,
                    'pos_y' => 12 + intdiv($i, 7) * 16,
                ]);
            }
        }

        // Un access point por pasillo de habitaciones.
        foreach (Ubicacion::where('tipo', 'pasillo')->with('area.piso.torre')->get() as $pasillo) {
            $piso = $pasillo->area->piso;
            Equipo::create([
                'ubicacion_id' => $pasillo->id,
                'tipo' => 'red',
                'codigo' => "AP-{$piso->torre->nombre}{$piso->numero}",
                'nombre' => "Access point pasillo {$piso->torre->nombre}-{$piso->numero}",
                'marca' => 'Ubiquiti',
                'modelo' => 'UniFi U6 Lite',
                'serie' => strtoupper(substr(md5("AP{$piso->id}"), 0, 10)),
                'pos_x' => 50,
                'pos_y' => 92,
            ]);
        }

        // Un televisor dado de baja, reemplazado, para ver el historial.
        Equipo::create([
            'habitacion_id' => Habitacion::where('numero', 'A-203')->value('id'),
            'tipo' => 'televisor',
            'codigo' => 'TV-A203-OLD',
            'nombre' => 'Televisor 32" (reemplazado)',
            'marca' => 'Kalley',
            'modelo' => 'K-LED32',
            'serie' => 'KL32X00981',
            'estado' => 'de_baja',
        ]);
    }
}
