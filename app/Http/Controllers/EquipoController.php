<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Equipo;
use App\Models\Torre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipoController extends Controller
{
    public function index(Request $request): View
    {
        $equipos = Equipo::query()
            ->with('ubicacion.area.piso.torre')
            ->when($request->filled('ubicacion_id'), fn ($q) => $q->where('ubicacion_id', $request->integer('ubicacion_id')))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->string('tipo')))
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('equipos.index', compact('equipos'));
    }

    public function create(): View
    {
        $torres = $this->torresConUbicaciones();
        $equipo = null;

        return view('equipos.create', compact('torres', 'equipo'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $equipo = Equipo::create($validated);
        Auditoria::registrar('crear_equipo', 'equipos', $equipo->id, $equipo->nombre);

        return redirect()->route('panel.equipos.index')->with('status', 'Equipo registrado.');
    }

    public function edit(Equipo $equipo): View
    {
        $torres = $this->torresConUbicaciones();

        return view('equipos.edit', compact('equipo', 'torres'));
    }

    public function update(Equipo $equipo, Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $equipo->update($validated);
        Auditoria::registrar('editar_equipo', 'equipos', $equipo->id, $equipo->nombre);

        return redirect()->route('panel.equipos.index')->with('status', 'Equipo actualizado.');
    }

    public function destroy(Equipo $equipo): RedirectResponse
    {
        if ($equipo->solicitudes()->exists()) {
            return back()->withErrors(['equipo' => 'No se puede eliminar: el equipo tiene solicitudes en su historial.']);
        }

        Auditoria::registrar('eliminar_equipo', 'equipos', $equipo->id, $equipo->nombre);
        $equipo->delete();

        return back()->with('status', 'Equipo eliminado.');
    }

    public function actualizarPosicion(Equipo $equipo, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pos_x' => ['required', 'numeric', 'between:0,100'],
            'pos_y' => ['required', 'numeric', 'between:0,100'],
        ]);

        $equipo->update($validated);
        Auditoria::registrar('reposicionar_equipo', 'equipos', $equipo->id, $equipo->nombre);

        $piso = $equipo->ubicacion->area->piso;

        return redirect()->route('plano.show', $piso)->with('status', 'Posición actualizada.');
    }

    public function ficha(Equipo $equipo): View
    {
        $equipo->load('ubicacion.area.piso.torre');
        $mantenimientos = $equipo->mantenimientos()->latest('fecha_cierre')->get();

        return view('equipos.ficha', compact('equipo', 'mantenimientos'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'ubicacion_id' => ['required', 'exists:ubicaciones,id'],
            'tipo' => ['required', 'in:pc,impresora,proyector,telefono,red,otro'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:100'],
            'usuario_asignado' => ['nullable', 'string', 'max:100'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'serie' => ['nullable', 'string', 'max:100'],
            'estado' => ['required', 'in:activo,de_baja'],
            'pos_x' => ['nullable', 'numeric', 'between:0,100'],
            'pos_y' => ['nullable', 'numeric', 'between:0,100'],
        ]);
    }

    private function torresConUbicaciones()
    {
        return Torre::with([
            'pisos' => fn ($q) => $q->orderBy('numero'),
            'pisos.areas' => fn ($q) => $q->orderBy('nombre'),
            'pisos.areas.ubicaciones' => fn ($q) => $q->orderBy('nombre'),
        ])->orderBy('nombre')->get();
    }
}
