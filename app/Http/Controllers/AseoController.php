<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Habitacion;
use App\Models\TareaAseo;
use App\Models\User;
use App\Services\Housekeeping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AseoController extends Controller
{
    public function __construct(private Housekeeping $housekeeping) {}

    /**
     * Personal de aseo: sus tareas y las que nadie ha tomado.
     * Recepción / admin: tablero completo para crear y asignar.
     */
    public function index(): View
    {
        $usuario = Auth::user();
        $esSupervisor = $usuario->tieneRol('recepcion');
        $con = ['habitacion.piso.torre', 'habitacion.reservaActual.huesped', 'asignadaA'];
        $orden = fn ($q) => $q->orderByRaw("CASE prioridad WHEN 'alta' THEN 0 ELSE 1 END")->orderBy('created_at');

        $abiertas = TareaAseo::with($con)
            ->where('estado', '!=', 'terminada')
            ->when(! $esSupervisor, fn ($q) => $q->where(fn ($q) => $q->whereNull('asignada_a')->orWhere('asignada_a', $usuario->id)))
            ->tap($orden)
            ->get();

        $terminadasHoy = TareaAseo::with($con)
            ->where('estado', 'terminada')
            ->whereDate('fin_at', today())
            ->when(! $esSupervisor, fn ($q) => $q->where('asignada_a', $usuario->id))
            ->latest('fin_at')
            ->get();

        $personal = User::where('rol', 'aseo')->where('activo', true)->orderBy('nombre')->get();
        $habitaciones = $esSupervisor ? Habitacion::orderBy('numero')->get(['id', 'numero', 'estado']) : collect();

        return view('aseo.index', compact('abiertas', 'terminadasHoy', 'personal', 'habitaciones', 'esSupervisor'));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'habitacion_id' => ['required', 'exists:habitaciones,id'],
            'tipo' => ['required', Rule::in(['salida', 'diaria', 'profunda', 'solicitud_huesped'])],
            'prioridad' => ['required', Rule::in(['normal', 'alta'])],
            'asignada_a' => ['nullable', Rule::exists('usuarios', 'id')->where('rol', 'aseo')],
            'notas' => ['nullable', 'string', 'max:500'],
        ]);

        $tarea = TareaAseo::create($datos + ['creada_por' => Auth::id()]);
        Auditoria::registrar('crear_tarea_aseo', 'tareas_aseo', $tarea->id, 'Hab. '.$tarea->habitacion->numero);

        return back()->with('status', 'Tarea de aseo creada.');
    }

    public function asignar(TareaAseo $tarea, Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'asignada_a' => ['nullable', Rule::exists('usuarios', 'id')->where('rol', 'aseo')],
        ]);

        if ($tarea->estado !== 'pendiente') {
            return back()->withErrors(['asignada_a' => 'Solo se reasignan tareas que no han empezado.']);
        }

        $tarea->update(['asignada_a' => $datos['asignada_a'] ?? null]);
        Auditoria::registrar('asignar_tarea_aseo', 'tareas_aseo', $tarea->id, $tarea->asignadaA?->nombre ?? 'sin asignar');

        return back()->with('status', 'Tarea asignada.');
    }

    public function iniciar(TareaAseo $tarea): RedirectResponse
    {
        $this->housekeeping->iniciar($tarea, Auth::id());

        return back()->with('status', 'Limpieza iniciada. Se registró la hora de inicio.');
    }

    public function terminar(TareaAseo $tarea, Request $request): RedirectResponse
    {
        $datos = $request->validate(['observaciones' => ['nullable', 'string', 'max:500']]);
        $this->housekeeping->terminar($tarea, Auth::id(), $datos['observaciones'] ?? null);

        return back()->with('status', 'Limpieza terminada. La habitación quedó lista.');
    }

    /** Registro del personal de aseo: cuántas habitaciones limpió cada persona, cuándo y en cuánto tiempo. */
    public function personal(Request $request): View
    {
        $desde = $request->date('desde') ?? today()->startOfMonth();
        $hasta = $request->date('hasta') ?? today();

        $terminadas = TareaAseo::with(['habitacion', 'asignadaA'])
            ->where('estado', 'terminada')
            ->whereBetween('fin_at', [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()])
            ->when($request->filled('usuario_id'), fn ($q) => $q->where('asignada_a', $request->integer('usuario_id')))
            ->latest('fin_at')
            ->get();

        $resumen = User::where('rol', 'aseo')->orderBy('nombre')->get()->map(function ($u) use ($terminadas) {
            $suyas = $terminadas->where('asignada_a', $u->id);

            return (object) [
                'usuario' => $u,
                'total' => $suyas->count(),
                'por_tipo' => $suyas->countBy('tipo'),
                'promedio' => $suyas->isEmpty() ? null : round($suyas->avg(fn ($t) => $t->minutos())),
                'minutos' => $suyas->sum(fn ($t) => $t->minutos()),
            ];
        });

        $personal = User::where('rol', 'aseo')->orderBy('nombre')->get();

        return view('aseo.personal', compact('terminadas', 'resumen', 'personal', 'desde', 'hasta'));
    }
}
