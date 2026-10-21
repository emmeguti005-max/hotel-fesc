<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureIsAdmin::class,
            'rol' => \App\Http\Middleware\EnsureRol::class,
        ]);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Reglas del negocio (habitación no disponible, pedido ya cerrado...) vuelven al
        // formulario como mensaje de error en vez de una página 500.
        $exceptions->render(function (\DomainException $e) {
            return back()->withInput()->withErrors(['operacion' => $e->getMessage()]);
        });
    })->create();
