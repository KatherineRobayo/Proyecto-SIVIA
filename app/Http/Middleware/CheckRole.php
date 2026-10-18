<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckRole Middleware
 *
 * Protege rutas según el rol del usuario autenticado.
 * Uso en rutas:
 *   ->middleware('role:admin')            — solo administradores
 *   ->middleware('role:admin,almacenista') — admin O almacenista
 *
 * Roles del sistema:
 *   admin       → acceso total (inventario, caja, reportes, registrar usuarios)
 *   almacenista → acceso restringido (inventario, pedidos, recibos — sin caja ni reportes)
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Roles permitidos (separados por coma en la definición de ruta)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // El usuario debe estar autenticado antes de llegar aquí
        // (la ruta también debe tener el middleware 'auth')
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Verificar si el rol del usuario esta entre los permitidos
        if (! in_array($request->user()->role, $roles, strict: true)) {
            // Si es una petición AJAX/API → respuesta JSON
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Acceso denegado.'], 403);
            }

            // redirigir al dashboard con mensaje de error
            return redirect()
                ->route('dashboard')
                ->with('error', 'No tienes permisos para acceder a esa sección.');
        }

        return $next($request);
    }
}
