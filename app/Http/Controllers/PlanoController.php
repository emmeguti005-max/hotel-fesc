<?php

namespace App\Http\Controllers;

use App\Models\Piso;
use App\Models\Torre;
use Illuminate\View\View;

class PlanoController extends Controller
{
    public function index(): View
    {
        $torres = Torre::with(['pisos' => fn ($q) => $q->where('numero', '!=', -1)->withCount('areas')->orderBy('numero', 'desc')])
            ->orderBy('nombre')
            ->get();

        $sotano = Piso::withCount('areas')->where('numero', -1)->first();

        return view('plano.index', compact('torres', 'sotano'));
    }

    public function show(Piso $piso): View
    {
        $piso->load([
            'torre',
            'areas' => fn ($q) => $q->orderBy('nombre'),
            'areas.ubicaciones.equipos' => fn ($q) => $q->where('estado', 'activo')->orderBy('nombre'),
        ]);

        $equipos = $piso->areas->flatMap(fn ($area) => $area->ubicaciones->flatMap->equipos);

        $posicionados = $equipos->filter(fn ($equipo) => $equipo->pos_x !== null && $equipo->pos_y !== null)->values();
        $sinPosicion = $equipos->reject(fn ($equipo) => $equipo->pos_x !== null && $equipo->pos_y !== null)->values();

        return view('plano.show', compact('piso', 'posicionados', 'sinPosicion'));
    }
}
