<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Personal del hotel: cada persona tiene un rol que define a qué partes del sistema entra. */
class PersonalController extends Controller
{
    public function index(Request $request): View
    {
        $usuarios = User::query()
            ->when($request->filled('rol'), fn ($q) => $q->where('rol', $request->string('rol')))
            ->orderByDesc('activo')
            ->orderBy('rol')
            ->orderBy('nombre')
            ->get();

        return view('personal.index', compact('usuarios'));
    }

    public function create(): View
    {
        return view('personal.form', ['usuario' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);
        $usuario = User::create($datos + ['activo' => true]);
        Auditoria::registrar('crear_usuario', 'usuarios', $usuario->id, "{$usuario->nombre} ({$usuario->rol})");

        return redirect()->route('panel.personal.index')->with('status', "{$usuario->nombre} agregado al personal.");
    }

    public function edit(User $usuario): View
    {
        return view('personal.form', compact('usuario'));
    }

    public function update(User $usuario, Request $request): RedirectResponse
    {
        $datos = $this->validar($request, $usuario);
        $datos['activo'] = $request->boolean('activo');

        if ($usuario->is(Auth::user()) && (! $datos['activo'] || $datos['rol'] !== 'admin')) {
            return back()->withErrors(['rol' => 'No puedes quitarte el rol de administrador ni desactivar tu propia cuenta.']);
        }

        if (empty($datos['password'])) {
            unset($datos['password']);
        }

        $usuario->update($datos);
        Auditoria::registrar('editar_usuario', 'usuarios', $usuario->id, "{$usuario->nombre} ({$usuario->rol})");

        return redirect()->route('panel.personal.index')->with('status', "{$usuario->nombre} actualizado.");
    }

    private function validar(Request $request, ?User $usuario = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['required', 'email', 'max:255', Rule::unique('usuarios', 'correo')->ignore($usuario?->id)],
            'rol' => ['required', Rule::in(['admin', 'recepcion', 'aseo', 'restaurante', 'mantenimiento'])],
            'cargo' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => [$usuario ? 'nullable' : 'required', Password::min(8)],
        ]);
    }
}
