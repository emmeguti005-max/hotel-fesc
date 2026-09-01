<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Solicitud;
use App\Models\Torre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolicitudController extends Controller
{
    public function create(): View
    {
        $torres = Torre::with([
            'pisos' => fn ($q) => $q->orderBy('numero'),
            'pisos.areas' => fn ($q) => $q->orderBy('nombre'),
            'pisos.areas.ubicaciones' => fn ($q) => $q->orderBy('nombre'),
            'pisos.areas.ubicaciones.equipos' => fn ($q) => $q->orderBy('nombre'),
        ])->orderBy('nombre')->get();

        return view('solicitudes.create', compact('torres'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ubicacion_id' => ['required', 'exists:ubicaciones,id'],
            'equipo_id' => ['nullable', 'exists:equipos,id'],
            'nombre_solicitante' => ['nullable', 'string', 'max:100'],
            'tipo' => ['required', 'in:impresora,instalacion,red,hardware,software,otro'],
            'descripcion' => ['required', 'string', 'max:2000'],
            'prioridad' => ['required', 'in:baja,media,alta,urgente'],
        ]);

        if (! empty($validated['equipo_id'])) {
            $equipo = Equipo::findOrFail($validated['equipo_id']);

            if ($equipo->ubicacion_id !== (int) $validated['ubicacion_id']) {
                return back()->withInput()->withErrors([
                    'equipo_id' => 'El equipo seleccionado no pertenece a esa ubicación.',
                ]);
            }
        }

        $solicitud = Solicitud::create([
            ...$validated,
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
}
