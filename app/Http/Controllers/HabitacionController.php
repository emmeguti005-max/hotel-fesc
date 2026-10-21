<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Habitacion;
use App\Models\Piso;
use App\Models\TareaAseo;
use App\Models\Torre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HabitacionController extends Controller
{
    /** Tablero de habitaciones agrupado por torre y piso, con el estado de cada una. */
    public function index(Request $request): View
    {
        $habitaciones = Habitacion::query()
            ->with(['piso.torre', 'reservaActual.huesped'])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->string('tipo')))
            ->when($request->filled('torre_id'), fn ($q) => $q->whereHas('piso', fn ($q) => $q->where('torre_id', $request->integer('torre_id'))))
            ->orderBy('numero')
            ->get();

        $grupos = $habitaciones
            ->sortBy(fn ($h) => sprintf('%s-%03d-%s', $h->piso->torre->nombre, $h->piso->numero + 1, $h->numero))
            ->groupBy(fn ($h) => $h->piso->etiqueta());

        $resumen = Habitacion::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $torres = Torre::orderBy('nombre')->get();

        return view('habitaciones.index', compact('grupos', 'resumen', 'torres'));
    }

    public function show(Habitacion $habitacion): View
    {
        $habitacion->load([
            'piso.torre',
            'reservaActual.huesped',
            'equipos' => fn ($q) => $q->orderBy('nombre'),
        ]);

        $reservas = $habitacion->reservas()->with('huesped')->latest('fecha_entrada')->limit(8)->get();
        $tareas = $habitacion->tareasAseo()->with('asignadaA')->latest()->limit(8)->get();
        $solicitudes = $habitacion->solicitudes()->latest()->limit(8)->get();
        $consumos = $habitacion->reservaActual
            ? $habitacion->reservaActual->pedidos()->latest()->get()
            : collect();

        return view('habitaciones.show', compact('habitacion', 'reservas', 'tareas', 'solicitudes', 'consumos'));
    }

    public function create(): View
    {
        return view('habitaciones.create', ['habitacion' => null, 'pisos' => $this->pisos()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $habitacion = Habitacion::create($this->validar($request));
        Auditoria::registrar('crear_habitacion', 'habitaciones', $habitacion->id, $habitacion->numero);

        return redirect()->route('panel.habitaciones.show', $habitacion)->with('status', 'Habitación creada.');
    }

    public function edit(Habitacion $habitacion): View
    {
        return view('habitaciones.edit', ['habitacion' => $habitacion, 'pisos' => $this->pisos()]);
    }

    public function update(Habitacion $habitacion, Request $request): RedirectResponse
    {
        $habitacion->update($this->validar($request, $habitacion));
        Auditoria::registrar('editar_habitacion', 'habitaciones', $habitacion->id, $habitacion->numero);

        return redirect()->route('panel.habitaciones.show', $habitacion)->with('status', 'Habitación actualizada.');
    }

    public function destroy(Habitacion $habitacion): RedirectResponse
    {
        if ($habitacion->reservas()->exists()) {
            return back()->withErrors(['habitacion' => 'No se puede eliminar: la habitación tiene reservas en su historial. Puedes bloquearla.']);
        }

        Auditoria::registrar('eliminar_habitacion', 'habitaciones', $habitacion->id, $habitacion->numero);
        $habitacion->delete();

        return redirect()->route('panel.habitaciones.index')->with('status', 'Habitación eliminada.');
    }

    /**
     * Cambios manuales de estado (recepción / mantenimiento). Ocupar y desocupar sólo
     * pasa por check-in / check-out, y la limpieza la cierra el personal de aseo.
     */
    public function cambiarEstado(Habitacion $habitacion, Request $request): RedirectResponse
    {
        $estado = $request->validate([
            'estado' => ['required', Rule::in(['disponible', 'sucia', 'mantenimiento', 'bloqueada'])],
        ])['estado'];

        if ($habitacion->estado === 'ocupada') {
            return back()->withErrors(['estado' => 'La habitación está ocupada: su estado cambia con el check-out.']);
        }

        if ($estado === 'disponible' && ! in_array($habitacion->estado, ['mantenimiento', 'bloqueada'], true)) {
            return back()->withErrors(['estado' => 'Para dejarla disponible, el personal de aseo debe terminar su limpieza.']);
        }

        $habitacion->update(['estado' => $estado]);

        if ($estado === 'sucia' && ! $habitacion->tareasAseo()->where('estado', '!=', 'terminada')->exists()) {
            TareaAseo::create([
                'habitacion_id' => $habitacion->id,
                'tipo' => 'profunda',
                'notas' => 'Marcada como sucia desde recepción.',
                'creada_por' => Auth::id(),
            ]);
        }

        Auditoria::registrar('estado_habitacion', 'habitaciones', $habitacion->id, "{$habitacion->numero} → {$estado}");

        return back()->with('status', "Habitación {$habitacion->numero}: {$estado}.");
    }

    private function validar(Request $request, ?Habitacion $habitacion = null): array
    {
        return $request->validate([
            'piso_id' => ['required', 'exists:pisos,id'],
            'numero' => ['required', 'string', 'max:20', Rule::unique('habitaciones', 'numero')->ignore($habitacion?->id)],
            'tipo' => ['required', Rule::in(['sencilla', 'doble', 'suite', 'familiar'])],
            'capacidad' => ['required', 'integer', 'between:1,10'],
            'tarifa_noche' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function pisos()
    {
        return Piso::with('torre')->where('numero', '>', 0)->get()
            ->sortBy(fn ($p) => $p->torre->nombre.sprintf('%03d', $p->numero))->values();
    }
}
