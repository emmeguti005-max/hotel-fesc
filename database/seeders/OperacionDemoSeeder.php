<?php

namespace Database\Seeders;

use App\Models\Equipo;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\Seguimiento;
use App\Models\Solicitud;
use App\Models\TareaAseo;
use App\Models\Ubicacion;
use App\Models\User;
use App\Services\Cocina;
use App\Services\Housekeeping;
use App\Services\Recepcion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Simula unas tres semanas de operación del hotel pasando por los mismos servicios
 * que usa la aplicación (check-in/out, cocina, aseo, facturación). Para fechar cada
 * evento en el pasado se "mueve el reloj" con Carbon::setTestNow().
 *
 * Deja el hotel en un estado de hoy realista: huéspedes hospedados, llegadas y
 * salidas pendientes, habitaciones sucias, pedidos en cocina y reportes abiertos.
 */
class OperacionDemoSeeder extends Seeder
{
    private Carbon $ahora;

    private Collection $productos;

    private array $staff = [];

    private int $huespedIndex = 0;

    private Collection $huespedes;

    public function __construct(
        private Recepcion $recepcion,
        private Cocina $cocina,
        private Housekeeping $housekeeping,
    ) {}

    public function run(): void
    {
        mt_srand(2026);
        $this->ahora = Carbon::now();
        $this->productos = Producto::where('disponible', true)->get();
        $this->staff = User::pluck('id', 'correo')->all();
        $this->huespedes = $this->crearHuespedes();

        // Los servicios auditan con Auth::id(); al sembrar no hay sesión, así que se actúa como el admin.
        Auth::setUser(User::where('correo', 'admin@hotel.local')->firstOrFail());

        $habitaciones = Habitacion::orderBy('numero')->get()->sortBy(fn () => mt_rand())->values();
        $historicas = $habitaciones->splice(0, 18);
        $actuales = $habitaciones->splice(0, 24);
        $salidasHoy = $habitaciones->splice(0, 3);
        $llegadasHoy = $habitaciones->splice(0, 4);
        $enMantenimiento = $habitaciones->shift();
        $bloqueada = $habitaciones->shift();

        // 1) Estadías ya terminadas (últimas ~3 semanas): con consumos, aseo diario, factura y aseo de salida.
        foreach ($historicas as $habitacion) {
            $noches = mt_rand(1, 4);
            $salida = $this->ahora->copy()->startOfDay()->subDays(mt_rand(1, 18));
            $this->estadiaCompleta($habitacion, $salida->copy()->subDays($noches), $salida);
        }

        // 2) Ventas del restaurante a clientes de paso (mesa / mostrador), ya facturadas.
        foreach (range(1, 22) as $i) {
            $dia = $this->ahora->copy()->startOfDay()->subDays(mt_rand(1, 20));
            $this->pedidoCompleto($dia->setTime(mt_rand(12, 20), mt_rand(0, 59)), [
                'origen' => mt_rand(0, 3) ? 'mesa' : 'mostrador',
                'mesa' => (string) mt_rand(1, 14),
                'cliente_nombre' => mt_rand(0, 2) ? null : $this->nombreSuelto(),
                'forma_pago' => ['efectivo', 'tarjeta', 'tarjeta', 'transferencia'][mt_rand(0, 3)],
            ]);
        }

        // 3) Huéspedes hospedados ahora mismo.
        foreach ($actuales as $habitacion) {
            $entrada = $this->ahora->copy()->startOfDay()->subDays(mt_rand(0, 3));
            $salida = $this->ahora->copy()->startOfDay()->addDays(mt_rand(0, 4));
            if ($salida->lte($entrada)) {
                $salida = $entrada->copy()->addDay();
            }
            $this->estadiaEnCurso($habitacion, $entrada, $salida);
        }

        // 4) Salieron esta mañana: habitación sucia esperando aseo (una ya en limpieza).
        foreach ($salidasHoy as $i => $habitacion) {
            $entrada = $this->ahora->copy()->startOfDay()->subDays(2);
            $reserva = $this->estadiaEnCurso($habitacion, $entrada, $this->ahora->copy()->startOfDay());
            $this->en($this->hace(150 - $i * 40), fn () => $this->recepcion->checkOut($reserva, 'tarjeta', $this->id('recepcion@hotel.local')));

            $tarea = TareaAseo::where('habitacion_id', $habitacion->id)->where('estado', 'pendiente')->first();
            if ($i === 0) {
                $this->en($this->hace(25), fn () => $this->housekeeping->iniciar($tarea, $this->id('aseo@hotel.local')));
            } elseif ($i === 1) {
                $tarea->update(['asignada_a' => $this->id('diana.vargas@hotel.local')]);
            }
        }

        // 5) Llegadas de hoy (reservadas, pendientes de check-in) y reservas futuras.
        foreach ($llegadasHoy as $habitacion) {
            $this->reservar($habitacion, $this->ahora->copy()->startOfDay(), $this->ahora->copy()->startOfDay()->addDays(mt_rand(1, 3)), $this->ahora->copy()->subDays(mt_rand(2, 10)));
        }
        foreach ($historicas->take(10) as $habitacion) {
            $entrada = $this->ahora->copy()->startOfDay()->addDays(mt_rand(1, 12));
            $this->reservar($habitacion, $entrada, $entrada->copy()->addDays(mt_rand(1, 4)), $this->ahora->copy()->subDays(mt_rand(0, 6)));
        }
        // Una reserva cancelada, para el historial.
        $cancelada = $this->reservar($historicas[12], $this->ahora->copy()->startOfDay()->addDays(2), $this->ahora->copy()->startOfDay()->addDays(4), $this->ahora->copy()->subDays(5));
        $this->en($this->hace(60 * 24 * 2), fn () => $this->recepcion->cancelar($cancelada));

        // 6) Aseo de hoy en habitaciones ocupadas: repasos hechos, pendientes y un pedido del huésped.
        $ocupadas = Habitacion::where('estado', 'ocupada')->get()->values();
        $camareras = ['aseo@hotel.local', 'diana.vargas@hotel.local', 'luzmery.castro@hotel.local'];
        foreach ($ocupadas->take(4) as $i => $habitacion) {
            $this->repasoDiario($habitacion, $this->hace(300 - $i * 50), $camareras[$i % 3]);
        }
        foreach ($ocupadas->slice(4, 3)->values() as $i => $habitacion) {
            TareaAseo::create([
                'habitacion_id' => $habitacion->id,
                'tipo' => 'diaria',
                'asignada_a' => $i === 0 ? $this->id('luzmery.castro@hotel.local') : null,
                'creada_por' => $this->id('recepcion@hotel.local'),
            ]);
        }
        $this->en($this->hace(15), fn () => TareaAseo::create([
            'habitacion_id' => $ocupadas[7]->id,
            'tipo' => 'solicitud_huesped',
            'prioridad' => 'alta',
            'notas' => 'Piden toallas adicionales y cambio de sábanas.',
        ]));

        // 7) Cocina en vivo: pedidos en cada etapa del flujo.
        $this->pedidosDeHoy($ocupadas);

        // 8) Mantenimiento: reportes en habitaciones y zonas comunes.
        $this->reportesMantenimiento($enMantenimiento, $ocupadas);
        $bloqueada->update(['estado' => 'bloqueada', 'descripcion' => $bloqueada->descripcion.' · En remodelación de baño']);

        Carbon::setTestNow();
    }

    /* ---------------------------------------------------------------- estadías */

    private function estadiaCompleta(Habitacion $habitacion, Carbon $entrada, Carbon $salida): void
    {
        $reserva = $this->estadiaEnCurso($habitacion, $entrada, $salida);

        $this->en($salida->copy()->setTime(mt_rand(9, 11), mt_rand(0, 59)), function () use ($reserva) {
            $this->recepcion->checkOut($reserva, ['efectivo', 'tarjeta', 'tarjeta', 'transferencia'][mt_rand(0, 3)], $this->id(mt_rand(0, 2) ? 'recepcion@hotel.local' : 'andres.rojas@hotel.local'));
        });

        $tarea = TareaAseo::where('habitacion_id', $habitacion->id)->where('estado', 'pendiente')->latest('id')->first();
        $camarera = $this->id(['aseo@hotel.local', 'diana.vargas@hotel.local', 'luzmery.castro@hotel.local'][mt_rand(0, 2)]);
        $inicio = Carbon::now()->addMinutes(mt_rand(15, 70));
        $this->en($inicio, fn () => $this->housekeeping->iniciar($tarea, $camarera));
        $this->en($inicio->copy()->addMinutes(mt_rand(30, 60)), fn () => $this->housekeeping->terminar(
            $tarea, $camarera, mt_rand(0, 4) ? null : ['Faltaba un control remoto, se repuso.', 'Se cambió bombillo del baño.', 'Huésped dejó objetos olvidados: entregados a recepción.'][mt_rand(0, 2)]
        ));
    }

    /** Crea la reserva, hace check-in y simula consumos y repasos de los días ya transcurridos. */
    private function estadiaEnCurso(Habitacion $habitacion, Carbon $entrada, Carbon $salida): Reserva
    {
        $reserva = $this->reservar($habitacion, $entrada, $salida, $entrada->copy()->subDays(mt_rand(1, 15)));

        $checkin = $this->limitar($entrada->copy()->setTime(mt_rand(14, 17), mt_rand(0, 59)));
        $this->en($checkin, fn () => $this->recepcion->checkIn($reserva, $this->id('recepcion@hotel.local')));

        for ($dia = $entrada->copy(); $dia->lt($salida) && $dia->lt($this->ahora->copy()->startOfDay()); $dia->addDay()) {
            if (mt_rand(1, 100) <= 60) {
                $this->pedidoCompleto($dia->copy()->setTime(mt_rand(18, 21), mt_rand(0, 59)), [
                    'origen' => 'habitacion',
                    'habitacion_id' => $habitacion->id,
                    'forma_pago' => 'cargo_habitacion',
                ], $reserva->huesped->nombre);
            }

            $siguiente = $dia->copy()->addDay();
            if ($siguiente->lt($salida) && $siguiente->lt($this->ahora->copy()->startOfDay()) && mt_rand(1, 100) <= 50) {
                $this->repasoDiario($habitacion, $siguiente->copy()->setTime(mt_rand(9, 13), mt_rand(0, 59)));
            }
        }

        return $reserva->fresh();
    }

    private function reservar(Habitacion $habitacion, Carbon $entrada, Carbon $salida, Carbon $creadaEl): Reserva
    {
        $huesped = $this->huespedes[$this->huespedIndex++ % $this->huespedes->count()];
        $personas = max(1, min($habitacion->capacidad, mt_rand(1, $habitacion->capacidad)));

        return $this->en($this->limitar($creadaEl), fn () => Reserva::create([
            'huesped_id' => $huesped->id,
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => $entrada->toDateString(),
            'fecha_salida' => $salida->toDateString(),
            'adultos' => $personas,
            'ninos' => $habitacion->tipo === 'familiar' ? mt_rand(0, 2) : 0,
            'tarifa_noche' => $habitacion->tarifa_noche,
            'estado' => 'reservada',
            'observaciones' => mt_rand(0, 4) ? null : ['Llega en vuelo nocturno.', 'Solicita cuna para bebé.', 'Aniversario: decoración sencilla.', 'Viaje de negocios, requiere factura a empresa.'][mt_rand(0, 3)],
            'creada_por' => $this->id(mt_rand(0, 1) ? 'recepcion@hotel.local' : 'andres.rojas@hotel.local'),
        ]));
    }

    private function repasoDiario(Habitacion $habitacion, Carbon $inicio, ?string $correo = null): void
    {
        $camarera = $this->id($correo ?? ['aseo@hotel.local', 'diana.vargas@hotel.local', 'luzmery.castro@hotel.local'][mt_rand(0, 2)]);
        $tarea = $this->en($inicio->copy()->subMinutes(mt_rand(30, 120)), fn () => TareaAseo::create([
            'habitacion_id' => $habitacion->id,
            'tipo' => 'diaria',
            'creada_por' => $this->id('marta.silva@hotel.local'),
        ]));
        $this->en($inicio, fn () => $this->housekeeping->iniciar($tarea, $camarera));
        $this->en($this->limitar($inicio->copy()->addMinutes(mt_rand(15, 30))), fn () => $this->housekeeping->terminar($tarea, $camarera));
    }

    /* ---------------------------------------------------------------- restaurante */

    /** Pedido que recorre todo el flujo: recibido → preparación → listo → despachado → entregado. */
    private function pedidoCompleto(Carbon $momento, array $datos, ?string $recibe = null): Pedido
    {
        $pedido = $this->nuevoPedido($momento, $datos);

        return $this->avanzarHasta($pedido, 'entregado', $momento, $recibe ?? ($datos['cliente_nombre'] ?? 'Cliente en mesa'));
    }

    private function nuevoPedido(Carbon $momento, array $datos): Pedido
    {
        $cantidades = [];
        foreach ($this->productos->random(mt_rand(1, 3)) as $producto) {
            $cantidades[$producto->id] = mt_rand(1, 2);
        }

        $tomadoPor = $datos['origen'] === 'habitacion'
            ? (mt_rand(0, 1) ? null : $this->id('recepcion@hotel.local')) // NULL = lo pidió el huésped por QR
            : $this->id(mt_rand(0, 1) ? 'sofia.mora@hotel.local' : 'pedro.castro@hotel.local');

        return $this->en($momento, fn () => $this->cocina->crearPedido($datos + [
            'notas' => mt_rand(0, 5) ? null : ['Sin cebolla.', 'Término medio.', 'Traer cubiertos para dos.', 'Bien caliente, por favor.'][mt_rand(0, 3)],
        ], $cantidades, $tomadoPor));
    }

    private function avanzarHasta(Pedido $pedido, string $estadoFinal, Carbon $desde, ?string $recibe = null): Pedido
    {
        $cocineros = ['restaurante@hotel.local', 'camila.torres@hotel.local'];
        $meseros = ['pedro.castro@hotel.local', 'sofia.mora@hotel.local'];
        $momento = $desde->copy();
        $minutos = ['en_preparacion' => [2, 6], 'listo' => [12, 25], 'despachado' => [1, 4], 'entregado' => [4, 9]];

        while ($pedido->estado !== $estadoFinal && isset(Pedido::SIGUIENTE[$pedido->estado])) {
            $siguiente = Pedido::SIGUIENTE[$pedido->estado];
            $momento = $this->limitar($momento->addMinutes(mt_rand(...$minutos[$siguiente])));
            $quien = in_array($siguiente, ['en_preparacion', 'listo'], true) ? $cocineros[mt_rand(0, 1)] : $meseros[mt_rand(0, 1)];
            $pedido = $this->en($momento, fn () => $this->cocina->avanzar($pedido, $this->id($quien), $siguiente === 'entregado' ? $recibe : null));
        }

        return $pedido;
    }

    private function pedidosDeHoy(Collection $ocupadas): void
    {
        // Ventas ya cerradas hoy.
        foreach (range(1, 4) as $i) {
            $this->pedidoCompleto($this->hace(240 - $i * 45), ['origen' => 'mesa', 'mesa' => (string) mt_rand(1, 14), 'forma_pago' => 'tarjeta']);
        }
        foreach ($ocupadas->slice(8, 3)->values() as $i => $habitacion) {
            $this->pedidoCompleto($this->hace(200 - $i * 40), ['origen' => 'habitacion', 'habitacion_id' => $habitacion->id, 'forma_pago' => 'cargo_habitacion'],
                $habitacion->reservaActual?->huesped?->nombre);
        }

        // En curso, uno por etapa del tablero de cocina.
        $enCurso = [
            ['recibido', 4, ['origen' => 'habitacion', 'habitacion_id' => $ocupadas[11]->id, 'forma_pago' => 'cargo_habitacion']],
            ['recibido', 2, ['origen' => 'mesa', 'mesa' => '5', 'forma_pago' => 'efectivo']],
            ['en_preparacion', 14, ['origen' => 'habitacion', 'habitacion_id' => $ocupadas[12]->id, 'forma_pago' => 'cargo_habitacion']],
            ['en_preparacion', 10, ['origen' => 'mostrador', 'cliente_nombre' => 'Para llevar · Juliana', 'forma_pago' => 'efectivo']],
            ['listo', 26, ['origen' => 'habitacion', 'habitacion_id' => $ocupadas[13]->id, 'forma_pago' => 'cargo_habitacion']],
            ['despachado', 33, ['origen' => 'habitacion', 'habitacion_id' => $ocupadas[14]->id, 'forma_pago' => 'cargo_habitacion']],
        ];

        foreach ($enCurso as [$estado, $haceMinutos, $datos]) {
            $inicio = $this->hace($haceMinutos);
            $pedido = $this->nuevoPedido($inicio, $datos);
            $this->avanzarHasta($pedido, $estado, $inicio);
        }
    }

    /* ---------------------------------------------------------------- mantenimiento */

    private function reportesMantenimiento(Habitacion $enMantenimiento, Collection $ocupadas): void
    {
        $tecnicos = [$this->id('mantenimiento@hotel.local'), $this->id('luis.ortega@hotel.local')];
        $equipoDe = fn (Habitacion $h, string $tipo) => Equipo::where('habitacion_id', $h->id)->where('tipo', $tipo)->value('id');
        $zona = fn (string $nombre) => Ubicacion::where('nombre', $nombre)->value('id');
        $equipoCodigo = fn (string $codigo) => Equipo::where('codigo', $codigo)->first();

        // [habitación | null, ubicación | null, equipo_id, tipo, descripción, prioridad, estado, horas atrás, técnico, seguimientos, solicitante]
        $casos = [
            [$enMantenimiento, null, $equipoDe($enMantenimiento, 'aire_acondicionado'), 'aire_acondicionado', 'El aire acondicionado gotea agua sobre la cama y no enfría.', 'alta', 'en_proceso', 20, 0, ['Se drenó la bandeja. Falta gas refrigerante; se pidió a proveedor.'], 'Recepción'],
            [$ocupadas[2], null, $equipoDe($ocupadas[2], 'televisor'), 'tv_entretenimiento', 'El televisor no muestra los canales de cable.', 'media', 'pendiente', 3, null, [], null],
            [$ocupadas[5], null, null, 'plomeria', 'La ducha sale con muy poca presión.', 'alta', 'en_proceso', 5, 1, ['Revisando regulador de presión del piso.'], null],
            [$ocupadas[9], null, $equipoDe($ocupadas[9], 'cerradura'), 'cerrajeria', 'La tarjeta no abre la puerta, toca pedir apertura en recepción.', 'urgente', 'pendiente', 1, null, [], null],
            [null, $zona('Piscina'), $equipoCodigo('PIS-01')?->id, 'otro', 'La motobomba hace ruido fuerte al arrancar.', 'media', 'pendiente', 30, null, [], 'Salvavidas'],
            [null, $zona('Cocina principal'), $equipoCodigo('COC-03')?->id, 'equipo_cocina', 'La nevera industrial no baja de 9 °C.', 'urgente', 'en_proceso', 3, 0, ['Se limpió el condensador.', 'Temperatura bajando, se deja en observación.'], 'Chef Jorge Díaz'],
            [null, $zona('Lavandería'), $equipoCodigo('LAV-02')?->id, 'electrico', 'La lavadora 2 se apaga a mitad de ciclo.', 'alta', 'resuelto', 70, 1, ['Breaker dañado en el tablero de lavandería.', 'Se reemplazó breaker de 30 A. Funciona normal.'], 'Ama de llaves'],
            [null, $zona('Lobby'), $equipoCodigo('AP-LOB-01')?->id, 'wifi', 'Huéspedes reportan que el wifi del lobby se cae.', 'media', 'resuelto', 96, 0, ['Se actualizó firmware del access point y se cambió el canal.'], 'Recepción'],
            [null, $zona('Gimnasio'), $equipoCodigo('GYM-01')?->id, 'mobiliario', 'La caminadora se detiene sola.', 'baja', 'no_resuelto', 150, 1, ['Tarjeta de control dañada. Se cotizó con el proveedor, pendiente aprobación de gerencia.'], null],
            [null, $zona('Salón de eventos Guasimales'), $equipoCodigo('EVT-01')?->id, 'electrico', 'El videobeam no enciende para el evento de mañana.', 'alta', 'resuelto', 50, 0, ['Lámpara fundida. Se instaló repuesto.'], 'Coordinación de eventos'],
            [null, $zona('Parqueadero'), $equipoCodigo('PAR-01')?->id, 'otro', 'La talanquera queda abierta.', 'media', 'resuelto', 200, 1, ['Se ajustó el final de carrera.'], 'Vigilancia'],
            [null, $zona('Restaurante Sabores del Norte'), null, 'mobiliario', 'Dos sillas del restaurante con la pata floja.', 'baja', 'pendiente', 40, null, [], 'Sofía Mora'],
        ];

        foreach ($casos as $n => [$habitacion, $ubicacionId, $equipoId, $tipo, $descripcion, $prioridad, $estado, $horas, $tec, $seguimientos, $solicitante]) {
            $creada = $this->hace($horas * 60);
            $tecnico = $tec !== null ? $tecnicos[$tec] : null;

            $solicitud = new Solicitud([
                'habitacion_id' => $habitacion?->id,
                'ubicacion_id' => $ubicacionId,
                'equipo_id' => $equipoId,
                'nombre_solicitante' => $solicitante ?? $habitacion?->reservaActual?->huesped?->nombre,
                'tipo' => $tipo,
                'descripcion' => $descripcion,
                'prioridad' => $prioridad,
                'estado' => $estado,
                'tecnico_id' => $tecnico,
                'fecha_asignacion' => $tecnico ? $creada->copy()->addMinutes(25) : null,
                'fecha_cierre' => in_array($estado, ['resuelto', 'no_resuelto'], true) ? $creada->copy()->addHours(max(1, intdiv($horas, 3))) : null,
                'ip_origen' => '192.168.10.'.(20 + $n),
            ]);
            $solicitud->created_at = $solicitud->updated_at = $creada;
            $solicitud->save();

            foreach ($seguimientos as $i => $comentario) {
                $seguimiento = new Seguimiento(['solicitud_id' => $solicitud->id, 'usuario_id' => $tecnico, 'comentario' => $comentario]);
                $seguimiento->created_at = $seguimiento->updated_at = $creada->copy()->addMinutes(40 + $i * 90);
                $seguimiento->save();
            }
        }

        $enMantenimiento->update(['estado' => 'mantenimiento']);
    }

    /* ---------------------------------------------------------------- utilidades */

    /** Ejecuta $accion con el reloj del sistema en $momento. */
    private function en(Carbon $momento, callable $accion): mixed
    {
        Carbon::setTestNow($momento);

        return $accion();
    }

    private function hace(int $minutos): Carbon
    {
        return $this->ahora->copy()->subMinutes($minutos);
    }

    /** Nada puede quedar fechado en el futuro respecto al momento real de correr el seeder. */
    private function limitar(Carbon $momento): Carbon
    {
        return $momento->gt($this->ahora) ? $this->ahora->copy()->subMinutes(mt_rand(1, 5)) : $momento;
    }

    private function id(string $correo): int
    {
        return $this->staff[$correo];
    }

    private function nombreSuelto(): string
    {
        return ['Juliana Pabón', 'Oscar Jaimes', 'Ingrid Sepúlveda', 'Hernán Ramírez', 'Natalia Contreras'][mt_rand(0, 4)];
    }

    private function crearHuespedes(): Collection
    {
        $datos = [
            ['María Fernanda Ortiz', 'CC', 'Bucaramanga'], ['Juan Carlos Peñaranda', 'CC', 'Cúcuta'], ['Valentina Rincón', 'CC', 'Bogotá'],
            ['Santiago Lizcano', 'CC', 'Pamplona'], ['Daniela Quintero', 'CC', 'Medellín'], ['Alejandro Villamizar', 'CC', 'Ocaña'],
            ['Paula Andrea Suárez', 'CC', 'Barranquilla'], ['José Luis Contreras', 'CE', 'San Cristóbal'], ['Carolina Mendoza', 'CC', 'Cali'],
            ['Miguel Ángel Parada', 'CC', 'Cúcuta'], ['Andrea Carolina Rueda', 'CC', 'Bucaramanga'], ['Ricardo Jaimes', 'CC', 'Bogotá'],
            ['Gabriela Sandoval', 'PAS', 'Caracas'], ['Felipe Gutiérrez', 'CC', 'Villa del Rosario'], ['Laura Camila Bautista', 'CC', 'Tunja'],
            ['Héctor Fabio Duarte', 'CC', 'Cúcuta'], ['Natalia Cárdenas', 'CC', 'Pereira'], ['Óscar Iván Pinzón', 'CC', 'Bogotá'],
            ['Mariana Delgado', 'CE', 'Mérida'], ['Sebastián Torres', 'CC', 'Cartagena'], ['Luisa Fernanda Rojas', 'CC', 'Manizales'],
            ['Camilo Andrés Prada', 'CC', 'Cúcuta'], ['Isabella Martínez', 'TI', 'Bucaramanga'], ['Diego Alejandro Carrillo', 'CC', 'Bogotá'],
            ['Ana Sofía Vera', 'CC', 'Pamplona'], ['Thomas Müller', 'PAS', 'Berlín'], ['Claudia Patricia León', 'CC', 'Ibagué'],
            ['Jorge Enrique Becerra', 'CC', 'Cúcuta'], ['Mónica Alexandra Celis', 'CC', 'Villavicencio'], ['Esteban Ramírez', 'CC', 'Medellín'],
            ['Sara Valentina Mora', 'CC', 'Bogotá'], ['Iván Darío Pacheco', 'CC', 'Santa Marta'], ['Juliana Andrea Rozo', 'CC', 'Cúcuta'],
            ['Emily Johnson', 'PAS', 'Houston'], ['Andrés Felipe Galvis', 'CC', 'Bucaramanga'], ['Karen Lorena Salazar', 'CC', 'Neiva'],
            ['Ramiro Antonio Celis', 'CC', 'Ocaña'], ['Viviana Marcela Ardila', 'CC', 'Bogotá'], ['Fernando José Arias', 'CE', 'Maracaibo'],
            ['Tatiana Gómez Peña', 'CC', 'Cúcuta'], ['Cristian David Leal', 'CC', 'Barrancabermeja'], ['Adriana Lucía Niño', 'CC', 'Sogamoso'],
            ['Manuel Santiago Ospina', 'CC', 'Armenia'], ['Lina María Toloza', 'CC', 'Cúcuta'], ['Pablo Emilio Sierra', 'CC', 'Bogotá'],
            ['Catalina Herrera', 'CC', 'Popayán'], ['Mauricio Jiménez', 'NIT', 'Bogotá'], ['Yuliana Castellanos', 'CC', 'Cúcuta'],
            ['Gustavo Adolfo Barrera', 'CC', 'Valledupar'], ['Daniel Esteban Ávila', 'CC', 'Bucaramanga'], ['Melissa Romero', 'CC', 'Montería'],
            ['Carlos Mario Uribe', 'CC', 'Medellín'], ['Paola Andrea Vargas', 'CC', 'Bogotá'], ['Nicolás Fuentes', 'PAS', 'Quito'],
            ['Diana Patricia Rangel', 'CC', 'Cúcuta'], ['Hugo Alberto Silva', 'CC', 'Tibú'], ['Marcela Lozano', 'CC', 'Cali'],
            ['Kevin Steven Ochoa', 'CC', 'Bogotá'], ['Rocío del Pilar Mejía', 'CC', 'Chinácota'], ['William Ferney Acosta', 'CC', 'Cúcuta'],
        ];

        return collect($datos)->map(function ($fila, $i) {
            [$nombre, $tipo, $ciudad] = $fila;
            $extranjero = in_array($ciudad, ['Caracas', 'San Cristóbal', 'Mérida', 'Maracaibo'], true) ? 'Venezuela'
                : (['Berlín' => 'Alemania', 'Houston' => 'Estados Unidos', 'Quito' => 'Ecuador'][$ciudad] ?? 'Colombia');
            $usuario = strtolower(str_replace(' ', '.', \Illuminate\Support\Str::ascii(\Illuminate\Support\Str::words($nombre, 2, ''))));

            return Huesped::create([
                'nombre' => $nombre,
                'tipo_documento' => $tipo,
                'documento' => $tipo === 'PAS' ? 'P'.(4800000 + $i * 731) : (string) (1090000000 + $i * 104729),
                'telefono' => '31'.mt_rand(0, 9).' '.mt_rand(100, 999).' '.mt_rand(1000, 9999),
                'correo' => mt_rand(0, 3) ? $usuario.'@correo.com' : null,
                'ciudad' => $ciudad,
                'pais' => $extranjero,
            ]);
        })->sortBy(fn () => mt_rand())->values();
    }
}
