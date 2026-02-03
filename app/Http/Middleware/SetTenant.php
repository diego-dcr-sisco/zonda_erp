<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Resetear el tenant al inicio de cada request
        TenantManager::setCurrentTenant(null);

        // Si el usuario está autenticado, establecer su tenant
        if (Auth::check()) {
            $user = Auth::user();
            
            Log::info('SetTenant Debug', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_tenant_id' => $user->tenant_id ?? 'NULL',
                'is_superAdmin' => $user->is_superAdmin ?? false,
            ]);

            // Si el usuario tiene tenant_id y es un super admin, permitir acceso sin tenant
            if ($user->is_superAdmin) {
                // Los super admins pueden acceder sin restricción de tenant
                if ($user->tenant_id) {
                    $tenant = Tenant::find($user->tenant_id);
                    if ($tenant) {
                        TenantManager::setCurrentTenant($tenant);
                        Log::info("SuperAdmin - Tenant establecido: {$tenant->id}");
                    }
                }
            } else {
                // Los usuarios normales deben tener un tenant_id válido
                if ($user->tenant_id) {
                    $tenant = Tenant::where('id', $user->tenant_id)
                        ->where('is_active', true)
                        ->first();

                    if ($tenant) {
                        TenantManager::setCurrentTenant($tenant);
                        Log::info("Usuario normal - Tenant establecido: {$tenant->id}");
                    } else {
                        Log::warning("Tenant no válido o inactivo para usuario: {$user->email}");
                    }
                } else {
                    Log::warning("Usuario sin tenant_id asignado: {$user->email}");
                }
            }
        } else {
            Log::info("SetTenant - No hay usuario autenticado");
        }

        Log::info('Tenant actual después de SetTenant', [
            'current_tenant_id' => TenantManager::getCurrentTenantId(),
        ]);

        return $next($request);
    }
}