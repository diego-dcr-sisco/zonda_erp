<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSingleSession
{
    /**
     * Handle an incoming request.
     * Valida que solo una sesión esté activa a la vez
     * Compatible con sesiones web y tokens de Sanctum para apps móviles
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Para peticiones de API con Sanctum (app móvil)
        if ($request->expectsJson() && Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
            $currentToken = $request->bearerToken();
            
            if ($user && $currentToken) {
                // Verificar si existe un token válido para este usuario usando PersonalAccessToken
                $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($currentToken);
                $hasValidToken = $tokenModel && $tokenModel->tokenable_id === $user->id;
                
                if (!$hasValidToken) {
                    // El token no es válido (fue revocado al iniciar sesión en otro dispositivo)
                    return response()->json([
                        'error' => 'Sesión inválida',
                        'message' => 'Esta cuenta está siendo usada en otro dispositivo. Por favor, inicia sesión nuevamente.',
                        'code' => 'SESSION_EXPIRED'
                    ], 401);
                }
            }
        }
        // Para sesiones web tradicionales
        elseif (Auth::check()) {
            $user = Auth::user();
            
            if ($user) {
                // Obtener el token de sesión almacenado en la base de datos
                $currentToken = $user->session_token;
                
                // Obtener el token de la sesión actual
                $sessionToken = $request->session()->get('user_session_token');
                
                // Si los tokens no coinciden, cerrar la sesión
                if ($currentToken && $sessionToken && $currentToken != $sessionToken) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    return redirect('/login')->withErrors([
                        'message' => 'Tu cuenta ha iniciado sesión en otro dispositivo.'
                    ]);
                }
            }
        }
        
        return $next($request);
    }
}