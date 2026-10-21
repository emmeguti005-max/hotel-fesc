<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Uso: ->middleware('rol:recepcion,restaurante'). El administrador siempre pasa. */
class EnsureRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()?->tieneRol(...$roles)) {
            abort(403, 'Tu rol no tiene acceso a esta sección.');
        }

        return $next($request);
    }
}
