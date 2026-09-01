<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalSolicitudes = Solicitud::count();

        $porEstado = $this->conteoPor('estado', ['pendiente', 'en_proceso', 'resuelto', 'no_resuelto']);
        $porTipo = $this->conteoPor('tipo', ['impresora', 'instalacion', 'red', 'hardware', 'software', 'otro']);
        $porPrioridad = $this->conteoPor('prioridad', ['baja', 'media', 'alta', 'urgente']);

        $tiempoPromedioHoras = Solicitud::whereNotNull('fecha_cierre')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, fecha_cierre)) as promedio'))
            ->value('promedio');

        $vencidas = Solicitud::vencidas()->count();

        $data = compact('totalSolicitudes', 'porEstado', 'porTipo', 'porPrioridad', 'tiempoPromedioHoras', 'vencidas');

        if (Auth::user()->isAdmin()) {
            $data['porTorre'] = Solicitud::query()
                ->join('ubicaciones', 'solicitudes.ubicacion_id', '=', 'ubicaciones.id')
                ->join('areas', 'ubicaciones.area_id', '=', 'areas.id')
                ->join('pisos', 'areas.piso_id', '=', 'pisos.id')
                ->join('torres', 'pisos.torre_id', '=', 'torres.id')
                ->select('torres.nombre', DB::raw('count(*) as total'))
                ->groupBy('torres.id', 'torres.nombre')
                ->orderByDesc('total')
                ->get();

            $data['topEquipos'] = Equipo::withCount('solicitudes')
                ->having('solicitudes_count', '>', 0)
                ->orderByDesc('solicitudes_count')
                ->limit(5)
                ->get();

            $data['rankingTecnicos'] = User::where('rol', 'tecnico')
                ->withCount(['solicitudesAsignadas as resueltas_count' => function ($q) {
                    $q->whereIn('estado', ['resuelto', 'no_resuelto']);
                }])
                ->orderByDesc('resueltas_count')
                ->get();
        }

        return view('panel.dashboard', $data);
    }

    private function conteoPor(string $columna, array $valores): array
    {
        $conteos = Solicitud::select($columna, DB::raw('count(*) as total'))
            ->groupBy($columna)
            ->pluck('total', $columna);

        return collect($valores)->mapWithKeys(fn ($valor) => [$valor => $conteos->get($valor, 0)])->all();
    }
}
