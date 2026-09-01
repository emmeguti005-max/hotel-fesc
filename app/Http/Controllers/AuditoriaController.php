<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $registros = Auditoria::query()
            ->with('usuario')
            ->when($request->filled('usuario_id'), fn ($q) => $q->where('usuario_id', $request->integer('usuario_id')))
            ->when($request->filled('accion'), fn ($q) => $q->where('accion', $request->string('accion')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha', '<=', $request->date('hasta')))
            ->latest('fecha')
            ->paginate(30)
            ->withQueryString();

        $usuarios = User::orderBy('nombre')->get();
        $acciones = Auditoria::query()->distinct()->orderBy('accion')->pluck('accion');

        return view('panel.auditoria', compact('registros', 'usuarios', 'acciones'));
    }
}
