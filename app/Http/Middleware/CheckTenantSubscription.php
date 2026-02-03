<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CheckTenantSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        
        $this->deactivateExpiredTenants();
    
        $user = Auth::user();
        if ($user?->tenant) {
            $tenant = $user->tenant;
            
            if (!$tenant->is_active) {
                
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->withErrors([
                    'message' => 'Su suscripción ha expirado. Por favor, contacte al administrador. ']);
            }
        }
        
        return $next($request);
    }

    protected function deactivateExpiredTenants()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return;
            }

            // Relación tenant 
            $tenant = $user->tenant;

            if (!$tenant) {
                return;
            }

            // Si el tenant está activo y tiene fecha de fin y está expirado
            $limit_date = $tenant->subscription_end->addDays(5);
            if ($tenant->is_active && $tenant->subscription_end && $limit_date  < now()) {
                $tenant->update(['is_active' => false]);
            }

        } catch (\Exception $e) {
            Log::error("Error en middleware CheckTenantSubscription: " . $e->getMessage());
        }
    }
}