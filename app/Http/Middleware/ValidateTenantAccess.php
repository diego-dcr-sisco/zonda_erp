<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ValidateTenantAccess
{
    /**
     * Valida que los parámetros tenant_id en la URL coincidan con el tenant del usuario
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Si no hay usuario autenticado, dejar pasar (el middleware 'auth' se encarga)
        if (!$user) {
            return $next($request);
        }

        // Si es super admin, permitir acceso sin restricciones
        if ($user->is_superAdmin) {
            return $next($request);
        }

        // Obtener tenant_id de la ruta o parámetros
        $routeTenantId = $request->route('tenant_id') ?? $request->input('tenant_id');

        // Si hay un tenant_id en la URL, validar que coincida con el del usuario
        if ($routeTenantId && $user->tenant_id != $routeTenantId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No tienes acceso a esta información.',
                    'error' => 'Acceso denegado'
                ], 403);
            }

            abort(403, 'No tienes acceso a esta información.');
        }

        return $next($request);
    }
}
