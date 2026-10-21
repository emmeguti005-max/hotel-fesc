<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('recordar'))) {
            return back()->withInput($request->only('correo'))->withErrors([
                'correo' => 'Credenciales incorrectas.',
            ]);
        }

        if (! Auth::user()->activo) {
            Auth::logout();

            return back()->withInput($request->only('correo'))->withErrors([
                'correo' => 'Esta cuenta está deshabilitada.',
            ]);
        }

        $request->session()->regenerate();

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'login',
            'entidad' => 'usuarios',
            'entidad_id' => Auth::id(),
            'ip' => $request->ip(),
            'fecha' => now(),
        ]);

        return redirect()->intended(Auth::user()->rutaInicio());
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
