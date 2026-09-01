<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Auditoria;
use App\Models\Piso;
use App\Models\Torre;
use App\Models\Ubicacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EstructuraController extends Controller
{
    public function torres(): View
    {
        $torres = Torre::withCount('pisos')->orderBy('nombre')->get();

        return view('estructura.torres', compact('torres'));
    }

    public function storeTorre(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
        ]);

        $torre = Torre::create($validated);
        Auditoria::registrar('crear_torre', 'torres', $torre->id, $torre->nombre);

        return back()->with('status', 'Torre creada.');
    }

    public function updateTorre(Torre $torre, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
        ]);

        $torre->update($validated);
        Auditoria::registrar('editar_torre', 'torres', $torre->id, $torre->nombre);

        return back()->with('status', 'Torre actualizada.');
    }

    public function destroyTorre(Torre $torre): RedirectResponse
    {
        if ($torre->pisos()->exists()) {
            return back()->withErrors(['torre' => 'No se puede eliminar: la torre tiene pisos.']);
        }

        Auditoria::registrar('eliminar_torre', 'torres', $torre->id, $torre->nombre);
        $torre->delete();

        return back()->with('status', 'Torre eliminada.');
    }

    public function pisos(Torre $torre): View
    {
        $pisos = $torre->pisos()->withCount('areas')->orderBy('numero')->get();
        $sotano = Piso::with('torre')->where('numero', -1)->first();

        return view('estructura.pisos', compact('torre', 'pisos', 'sotano'));
    }

    public function storePiso(Torre $torre, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'numero' => ['required', 'integer', 'between:-1,8'],
        ]);

        if ($validated['numero'] == -1 && Piso::where('numero', -1)->exists()) {
            return back()->withErrors(['numero' => 'Ya existe un sótano, compartido por las tres torres.']);
        }

        $piso = $torre->pisos()->create($validated);
        Auditoria::registrar('crear_piso', 'pisos', $piso->id, "Torre {$torre->nombre}, piso {$piso->numero}");

        return back()->with('status', 'Piso creado.');
    }

    public function destroyPiso(Torre $torre, Piso $piso): RedirectResponse
    {
        if ($piso->areas()->exists()) {
            return back()->withErrors(['piso' => 'No se puede eliminar: el piso tiene áreas.']);
        }

        Auditoria::registrar('eliminar_piso', 'pisos', $piso->id, "Torre {$torre->nombre}, piso {$piso->numero}");
        $piso->delete();

        return back()->with('status', 'Piso eliminado.');
    }

    public function storePlanoPiso(Torre $torre, Piso $piso, Request $request): RedirectResponse
    {
        $request->validate([
            'plano' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($piso->plano_imagen) {
            Storage::disk('public')->delete($piso->plano_imagen);
        }

        $piso->update(['plano_imagen' => $request->file('plano')->store('planos', 'public')]);
        Auditoria::registrar('subir_plano_piso', 'pisos', $piso->id, "Torre {$torre->nombre}, piso {$piso->numero}");

        return back()->with('status', 'Plano actualizado.');
    }

    public function destroyPlanoPiso(Torre $torre, Piso $piso): RedirectResponse
    {
        if ($piso->plano_imagen) {
            Storage::disk('public')->delete($piso->plano_imagen);
            $piso->update(['plano_imagen' => null]);
            Auditoria::registrar('eliminar_plano_piso', 'pisos', $piso->id, "Torre {$torre->nombre}, piso {$piso->numero}");
        }

        return back()->with('status', 'Plano eliminado.');
    }

    public function areas(Torre $torre, Piso $piso): View
    {
        $areas = $piso->areas()->withCount('ubicaciones')->orderBy('nombre')->get();

        return view('estructura.areas', compact('torre', 'piso', 'areas'));
    }

    public function storeArea(Torre $torre, Piso $piso, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        $area = $piso->areas()->create($validated);
        Auditoria::registrar('crear_area', 'areas', $area->id, $area->nombre);

        return back()->with('status', 'Área creada.');
    }

    public function updateArea(Torre $torre, Piso $piso, Area $area, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        $area->update($validated);
        Auditoria::registrar('editar_area', 'areas', $area->id, $area->nombre);

        return back()->with('status', 'Área actualizada.');
    }

    public function destroyArea(Torre $torre, Piso $piso, Area $area): RedirectResponse
    {
        if ($area->ubicaciones()->exists()) {
            return back()->withErrors(['area' => 'No se puede eliminar: el área tiene ubicaciones.']);
        }

        Auditoria::registrar('eliminar_area', 'areas', $area->id, $area->nombre);
        $area->delete();

        return back()->with('status', 'Área eliminada.');
    }

    public function ubicaciones(Torre $torre, Piso $piso, Area $area): View
    {
        $ubicaciones = $area->ubicaciones()->withCount('equipos')->orderBy('nombre')->get();

        return view('estructura.ubicaciones', compact('torre', 'piso', 'area', 'ubicaciones'));
    }

    public function storeUbicacion(Torre $torre, Piso $piso, Area $area, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'in:consultorio,oficina,pasillo,sala,deposito,otro'],
        ]);

        $ubicacion = $area->ubicaciones()->create($validated);
        Auditoria::registrar('crear_ubicacion', 'ubicaciones', $ubicacion->id, $ubicacion->nombre);

        return back()->with('status', 'Ubicación creada.');
    }

    public function updateUbicacion(Torre $torre, Piso $piso, Area $area, Ubicacion $ubicacion, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'in:consultorio,oficina,pasillo,sala,deposito,otro'],
        ]);

        $ubicacion->update($validated);
        Auditoria::registrar('editar_ubicacion', 'ubicaciones', $ubicacion->id, $ubicacion->nombre);

        return back()->with('status', 'Ubicación actualizada.');
    }

    public function destroyUbicacion(Torre $torre, Piso $piso, Area $area, Ubicacion $ubicacion): RedirectResponse
    {
        if ($ubicacion->equipos()->exists() || $ubicacion->solicitudes()->exists()) {
            return back()->withErrors(['ubicacion' => 'No se puede eliminar: tiene equipos o solicitudes asociadas.']);
        }

        Auditoria::registrar('eliminar_ubicacion', 'ubicaciones', $ubicacion->id, $ubicacion->nombre);
        $ubicacion->delete();

        return back()->with('status', 'Ubicación eliminada.');
    }
}
