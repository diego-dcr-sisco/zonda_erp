<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequireTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Solo verificar si el usuario está autenticado
        if (Auth::check()) {
            $user = Auth::user();

            // Si el usuario NO tiene tenant_id y NO es super admin, denegar acceso
            if (!$user->tenant_id && !$user->is_superAdmin) {
                Log::warning("Acceso denegado - Usuario sin tenant_id", [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);

                // Si es una petición AJAX/API, retornar JSON
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'No tienes acceso a ningún tenant. Contacta al administrador.',
                        'status' => 'error'
                    ], 403);
                }

                // Si es una petición web, redirigir a una página de error o logout
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->with('error', 'Tu cuenta no está asignada a ningún tenant. Contacta al administrador.');
            }
        }

        return $next($request);
    }
}