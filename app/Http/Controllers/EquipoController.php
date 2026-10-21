<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipoRequest;
use App\Http\Requests\PosicionEquipoRequest;
use App\Models\Auditoria;
use App\Models\Equipo;
use App\Models\Habitacion;
use App\Models\Torre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Inventario de activos del hotel: dotación de habitaciones y equipos de zonas comunes. */
class EquipoController extends Controller
{
    public function index(Request $request): View
    {
        $equipos = Equipo::query()
            ->with(['ubicacion.area.piso.torre', 'habitacion.piso.torre'])
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->string('tipo')))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
            ->when($request->filled('lugar'), fn ($q) => $request->input('lugar') === 'habitacion'
                ? $q->whereNotNull('habitacion_id')
                : $q->whereNull('habitacion_id'))
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = '%'.$request->string('q').'%';
                $q->where(fn ($q) => $q->where('nombre', 'like', $texto)
                    ->orWhere('codigo', 'like', $texto)
                    ->orWhere('serie', 'like', $texto)
                    ->orWhereHas('habitacion', fn ($q) => $q->where('numero', 'like', $texto)));
            })
            ->orderBy('codigo')
            ->paginate(30)
            ->withQueryString();

        $totales = Equipo::selectRaw('tipo, count(*) as total')->groupBy('tipo')->pluck('total', 'tipo');

        return view('equipos.index', compact('equipos', 'totales'));
    }

    public function create(): View
    {
        return view('equipos.create', $this->datosFormulario() + ['equipo' => null]);
    }

    public function store(EquipoRequest $request): RedirectResponse
    {
        $equipo = Equipo::create($request->validated());
        Auditoria::registrar('crear_equipo', 'equipos', $equipo->id, $equipo->nombre);

        return redirect()->route('panel.equipos.index')->with('status', 'Activo registrado.');
    }

    public function edit(Equipo $equipo): View
    {
        return view('equipos.edit', $this->datosFormulario() + ['equipo' => $equipo]);
    }

    public function update(Equipo $equipo, EquipoRequest $request): RedirectResponse
    {
        $equipo->update($request->validated());
        Auditoria::registrar('editar_equipo', 'equipos', $equipo->id, $equipo->nombre);

        return redirect()->route('panel.equipos.index')->with('status', 'Activo actualizado.');
    }

    public function destroy(Equipo $equipo): RedirectResponse
    {
        $eliminado = DB::transaction(function () use ($equipo) {
            if ($equipo->solicitudes()->exists()) {
                return false;
            }

            Auditoria::registrar('eliminar_equipo', 'equipos', $equipo->id, $equipo->nombre);
            $equipo->delete();

            return true;
        });

        if (! $eliminado) {
            return back()->withErrors(['equipo' => 'No se puede eliminar: el activo tiene reportes de mantenimiento en su historial. Puedes darlo de baja.']);
        }

        return back()->with('status', 'Activo eliminado.');
    }

    public function actualizarPosicion(Equipo $equipo, PosicionEquipoRequest $request): RedirectResponse
    {
        $equipo->update($request->validated());
        Auditoria::registrar('reposicionar_equipo', 'equipos', $equipo->id, $equipo->nombre);

        return redirect()->route('plano.show', $equipo->piso())->with('status', 'Posición actualizada.');
    }

    public function ficha(Equipo $equipo): View
    {
        $equipo->load(['ubicacion.area.piso.torre', 'habitacion.piso.torre']);
        $mantenimientos = $equipo->mantenimientos()->latest('fecha_cierre')->get();

        return view('equipos.ficha', compact('equipo', 'mantenimientos'));
    }

    private function datosFormulario(): array
    {
        return [
            'torres' => Torre::with([
                'pisos' => fn ($q) => $q->orderBy('numero'),
                'pisos.areas' => fn ($q) => $q->orderBy('nombre'),
                'pisos.areas.ubicaciones' => fn ($q) => $q->orderBy('nombre'),
            ])->orderBy('nombre')->get(),
            'habitaciones' => Habitacion::orderBy('numero')->get(['id', 'numero']),
        ];
    }
}
