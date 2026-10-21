<?php

namespace App\Http\Controllers;

use App\Http\Requests\AreaRequest;
use App\Http\Requests\PisoRequest;
use App\Http\Requests\PlanoPisoRequest;
use App\Http\Requests\TorreRequest;
use App\Http\Requests\UbicacionRequest;
use App\Models\Area;
use App\Models\Auditoria;
use App\Models\Piso;
use App\Models\Torre;
use App\Models\Ubicacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EstructuraController extends Controller
{
    public function torres(): View
    {
        $torres = Torre::withCount('pisos')->orderBy('nombre')->get();

        return view('estructura.torres', compact('torres'));
    }

    public function storeTorre(TorreRequest $request): RedirectResponse
    {
        $torre = Torre::create($request->validated());
        Auditoria::registrar('crear_torre', 'torres', $torre->id, $torre->nombre);

        return back()->with('status', 'Torre creada.');
    }

    public function updateTorre(Torre $torre, TorreRequest $request): RedirectResponse
    {
        $torre->update($request->validated());
        Auditoria::registrar('editar_torre', 'torres', $torre->id, $torre->nombre);

        return back()->with('status', 'Torre actualizada.');
    }

    public function destroyTorre(Torre $torre): RedirectResponse
    {
        $eliminada = DB::transaction(function () use ($torre) {
            if ($torre->pisos()->exists()) {
                return false;
            }

            Auditoria::registrar('eliminar_torre', 'torres', $torre->id, $torre->nombre);
            $torre->delete();

            return true;
        });

        if (! $eliminada) {
            return back()->withErrors(['torre' => 'No se puede eliminar: la torre tiene pisos.']);
        }

        return back()->with('status', 'Torre eliminada.');
    }

    public function pisos(Torre $torre): View
    {
        $pisos = $torre->pisos()->withCount(['areas', 'habitaciones'])->orderBy('numero')->get();
        $sotano = Piso::with('torre')->where('numero', -1)->first();

        return view('estructura.pisos', compact('torre', 'pisos', 'sotano'));
    }

    public function storePiso(Torre $torre, PisoRequest $request): RedirectResponse
    {
        $piso = $torre->pisos()->create($request->validated());
        Auditoria::registrar('crear_piso', 'pisos', $piso->id, "Torre {$torre->nombre}, piso {$piso->numero}");

        return back()->with('status', 'Piso creado.');
    }

    public function destroyPiso(Torre $torre, Piso $piso): RedirectResponse
    {
        $eliminado = DB::transaction(function () use ($torre, $piso) {
            if ($piso->areas()->exists() || $piso->habitaciones()->exists()) {
                return false;
            }

            Auditoria::registrar('eliminar_piso', 'pisos', $piso->id, "Torre {$torre->nombre}, piso {$piso->numero}");
            $piso->delete();

            return true;
        });

        if (! $eliminado) {
            return back()->withErrors(['piso' => 'No se puede eliminar: el piso tiene zonas comunes o habitaciones.']);
        }

        return back()->with('status', 'Piso eliminado.');
    }

    public function storePlanoPiso(Torre $torre, Piso $piso, PlanoPisoRequest $request): RedirectResponse
    {
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

    public function storeArea(Torre $torre, Piso $piso, AreaRequest $request): RedirectResponse
    {
        $area = $piso->areas()->create($request->validated());
        Auditoria::registrar('crear_area', 'areas', $area->id, $area->nombre);

        return back()->with('status', 'Área creada.');
    }

    public function updateArea(Torre $torre, Piso $piso, Area $area, AreaRequest $request): RedirectResponse
    {
        $area->update($request->validated());
        Auditoria::registrar('editar_area', 'areas', $area->id, $area->nombre);

        return back()->with('status', 'Área actualizada.');
    }

    public function destroyArea(Torre $torre, Piso $piso, Area $area): RedirectResponse
    {
        $eliminada = DB::transaction(function () use ($area) {
            if ($area->ubicaciones()->exists()) {
                return false;
            }

            Auditoria::registrar('eliminar_area', 'areas', $area->id, $area->nombre);
            $area->delete();

            return true;
        });

        if (! $eliminada) {
            return back()->withErrors(['area' => 'No se puede eliminar: el área tiene ubicaciones.']);
        }

        return back()->with('status', 'Área eliminada.');
    }

    public function ubicaciones(Torre $torre, Piso $piso, Area $area): View
    {
        $ubicaciones = $area->ubicaciones()->withCount('equipos')->orderBy('nombre')->get();

        return view('estructura.ubicaciones', compact('torre', 'piso', 'area', 'ubicaciones'));
    }

    public function storeUbicacion(Torre $torre, Piso $piso, Area $area, UbicacionRequest $request): RedirectResponse
    {
        $ubicacion = $area->ubicaciones()->create($request->validated());
        Auditoria::registrar('crear_ubicacion', 'ubicaciones', $ubicacion->id, $ubicacion->nombre);

        return back()->with('status', 'Ubicación creada.');
    }

    public function updateUbicacion(Torre $torre, Piso $piso, Area $area, Ubicacion $ubicacion, UbicacionRequest $request): RedirectResponse
    {
        $ubicacion->update($request->validated());
        Auditoria::registrar('editar_ubicacion', 'ubicaciones', $ubicacion->id, $ubicacion->nombre);

        return back()->with('status', 'Ubicación actualizada.');
    }

    public function destroyUbicacion(Torre $torre, Piso $piso, Area $area, Ubicacion $ubicacion): RedirectResponse
    {
        $eliminada = DB::transaction(function () use ($ubicacion) {
            if ($ubicacion->equipos()->exists() || $ubicacion->solicitudes()->exists()) {
                return false;
            }

            Auditoria::registrar('eliminar_ubicacion', 'ubicaciones', $ubicacion->id, $ubicacion->nombre);
            $ubicacion->delete();

            return true;
        });

        if (! $eliminada) {
            return back()->withErrors(['ubicacion' => 'No se puede eliminar: tiene activos o reportes de mantenimiento asociados.']);
        }

        return back()->with('status', 'Ubicación eliminada.');
    }
}
