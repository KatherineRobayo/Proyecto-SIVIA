<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// [HOTFIX: Corrección de Middleware Role] -> Registrando alias en bootstrap/app.php
// Sin este bloque, todas las rutas con ->middleware('role:admin') fallan con
// "Target class [role] does not exist" en Laravel 12/13.

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // [HOTFIX: Alias de Middleware] -> mapea 'role' al CheckRole::class
        // permite usar ->middleware('role:admin') y ->middleware('role:admin,almacenista')
        // en cualquier ruta de web.php sin errores de clase no encontrada.
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // [SECURITY: Response Middleware] -> SecurityHeaders
        // Inyecta headers HTTP críticos de seguridad en TODAS las responses.
        // Previene clickjacking, MIME sniffing, XSS, information disclosure.
        // Se ejecuta globalmente (append=prepend a response pipeline).
        //$middleware->append(\App\Http\Middleware\SecurityHeaders::class);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
