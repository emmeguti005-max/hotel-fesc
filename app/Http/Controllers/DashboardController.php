<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Pedido;
use App\Models\Reserva;
use App\Models\Solicitud;
use App\Models\TareaAseo;
use App\Models\User;
use App\Support\Etiquetas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** /panel lleva a cada quien a su pantalla de trabajo. */
    public function inicio(): RedirectResponse
    {
        return redirect(Auth::user()->rutaInicio());
    }

    public function index(): View
    {
        $hoy = today();
        $inicioMes = $hoy->copy()->startOfMonth();

        $conteos = Habitacion::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $porEstado = collect(Etiquetas::ESTADOS_HABITACION)->map(fn ($_, $estado) => (int) $conteos->get($estado, 0))->all();
        $totalHabitaciones = array_sum($porEstado);
        $vendibles = $totalHabitaciones - $porEstado['mantenimiento'] - $porEstado['bloqueada'];
        $ocupacion = $vendibles > 0 ? round($porEstado['ocupada'] / $vendibles * 100) : 0;

        $llegadas = Reserva::with(['huesped', 'habitacion'])
            ->where('estado', 'reservada')->whereDate('fecha_entrada', '<=', $hoy)
            ->orderBy('fecha_entrada')->get();

        $salidas = Reserva::with(['huesped', 'habitacion'])
            ->where('estado', 'en_curso')->whereDate('fecha_salida', '<=', $hoy)
            ->orderBy('fecha_salida')->get();

        $facturasMes = Factura::where('estado', 'emitida')->where('created_at', '>=', $inicioMes)->get(['total', 'tipo', 'created_at']);
        $ingresosHoy = $facturasMes->filter(fn ($f) => $f->created_at->isSameDay($hoy))->sum('total');
        $ingresosMes = $facturasMes->sum('total');
        $ingresosPorTipo = [
            'hospedaje' => $facturasMes->where('tipo', 'hospedaje')->sum('total'),
            'restaurante' => $facturasMes->where('tipo', 'restaurante')->sum('total'),
        ];

        $ultimos7 = collect(range(6, 0))->mapWithKeys(function ($dias) use ($hoy) {
            $dia = $hoy->copy()->subDays($dias);

            return [$dia->format('d/m') => (float) Factura::where('estado', 'emitida')->whereDate('created_at', $dia)->sum('total')];
        });

        $pedidosActivos = Pedido::whereNotIn('estado', ['entregado', 'cancelado'])->count();
        $aseoPendiente = TareaAseo::where('estado', '!=', 'terminada')->count();
        $mantenimientoAbierto = Solicitud::whereIn('estado', ['pendiente', 'en_proceso'])->count();
        $vencidas = Solicitud::vencidas()->count();

        $rankingAseo = User::where('rol', 'aseo')->where('activo', true)
            ->with(['tareasAseo' => fn ($q) => $q->where('estado', 'terminada')->where('fin_at', '>=', $inicioMes)])
            ->orderBy('nombre')->get()
            ->map(fn ($u) => (object) [
                'nombre' => $u->nombre,
                'total' => $u->tareasAseo->count(),
                'promedio' => $u->tareasAseo->isEmpty() ? null : round($u->tareasAseo->avg(fn ($t) => $t->minutos())),
            ])->sortByDesc('total')->values();

        $rankingDespachos = User::whereIn('rol', ['restaurante', 'admin'])
            ->withCount(['pedidosDespachados as total' => fn ($q) => $q->where('despachado_at', '>=', $inicioMes)])
            ->get()->where('total', '>', 0)->sortByDesc('total')->values();

        return view('panel.dashboard', compact(
            'porEstado', 'totalHabitaciones', 'ocupacion', 'llegadas', 'salidas', 'ingresosHoy', 'ingresosMes',
            'ingresosPorTipo', 'ultimos7', 'pedidosActivos', 'aseoPendiente', 'mantenimientoAbierto', 'vencidas',
            'rankingAseo', 'rankingDespachos',
        ));
    }
}
