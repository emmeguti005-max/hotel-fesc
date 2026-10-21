<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSolicitudRequest;
use App\Models\Habitacion;
use App\Models\Solicitud;
use App\Models\Torre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolicitudController extends Controller
{
    /** Reporte de daños: en una habitación o en una zona común (lobby, piscina, cocina...). */
    public function create(Request $request): View
    {
        $torres = Torre::with([
            'pisos' => fn ($q) => $q->orderBy('numero'),
            'pisos.areas' => fn ($q) => $q->orderBy('nombre'),
            'pisos.areas.ubicaciones' => fn ($q) => $q->orderBy('nombre'),
            'pisos.areas.ubicaciones.equipos' => fn ($q) => $q->where('estado', 'activo')->orderBy('nombre'),
        ])->orderBy('nombre')->get();

        $habitaciones = Habitacion::with(['equipos' => fn ($q) => $q->where('estado', 'activo')->orderBy('nombre')])
            ->orderBy('numero')
            ->get(['id', 'numero']);

        $habitacionElegida = $request->integer('habitacion_id') ?: null;

        return view('solicitudes.create', compact('torres', 'habitaciones', 'habitacionElegida'));
    }

    public function store(StoreSolicitudRequest $request): RedirectResponse
    {
        $solicitud = Solicitud::create([
            ...$request->validated(),
            'estado' => 'pendiente',
            'ip_origen' => $request->ip(),
        ]);

        return redirect()
            ->route('solicitudes.gracias', $solicitud)
            ->with('solicitud_id', $solicitud->id);
    }

    public function gracias(Solicitud $solicitud): View
    {
        return view('solicitudes.gracias', compact('solicitud'));
    }

    public function comprobante(Solicitud $solicitud): View|RedirectResponse
    {
        if (! in_array($solicitud->estado, ['resuelto', 'no_resuelto'], true)) {
            return redirect()->route('solicitudes.gracias', $solicitud);
        }

        $solicitud->load(['equipo', 'ubicacion.area.piso.torre', 'habitacion.piso.torre', 'tecnico']);
        $notaCierre = $solicitud->seguimientos()->latest()->first();

        return view('solicitudes.comprobante', compact('solicitud', 'notaCierre'));
    }
}
