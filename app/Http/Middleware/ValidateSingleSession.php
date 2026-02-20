<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
            // Si el usuario tiene un session_token y no coincide con ningún token activo
            if ($user->session_token) {
                // Si no hay token en el Authorization header, considerar sesión inválida
                if (!$currentToken) {
                    return response()->json([
                        'error' => 'Sesión inválida',
                        'message' => 'Esta cuenta está siendo usada en otro dispositivo. Por favor, inicia sesión nuevamente.'
                    ], 401);
                }

                // Verificar si existe un token válido para este usuario usando el modelo de Sanctum
                $hashedToken = hash('sha256', $currentToken);
                $hasValidToken = PersonalAccessToken::where('token', $hashedToken)
                    ->where('tokenable_type', get_class($user))
                    ->where('tokenable_id', $user->id)
                    ->exists();

                if (!$hasValidToken) {
                    return response()->json([
                        'error' => 'Sesión inválida',
                        'message' => 'Esta cuenta está siendo usada en otro dispositivo. Por favor, inicia sesión nuevamente.'
                    ], 401);
                }
            }
