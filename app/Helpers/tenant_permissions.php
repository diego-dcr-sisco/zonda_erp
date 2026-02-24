<?php

use App\Services\TenantPermissionFilter;
use App\Tenancy\TenantManager;

/**
 * Helper principal - usa trait hasTenantPermission
 */
if (!function_exists('tenant_can')) {
    function tenant_can($permission)
    {
        if (!auth()->check()) { 
            return false;
        }
        
        return auth()->user()->hasTenantPermission($permission);
    }
}

/**
 * Helper para múltiples permisos (any)
 */
if (!function_exists('tenant_any')) {
    function tenant_any(...$permissions)
    {
        if (!auth()->check()) {
            return false;
        }
        
        return auth()->user()->hasAnyTenantPermission(...$permissions);
    }
}

/**
 * Helper para todos los permisos (all)
 */
if (!function_exists('tenant_all')) {
    function tenant_all(...$permissions)
    {
        if (!auth()->check()) {
            return false;
        }
        
        return auth()->user()->hasAllTenantPermissions(...$permissions);
    }
}

/**
 * Helper para obtener permisos permitidos
 */
if (!function_exists('tenant_permissions')) {
    function tenant_permissions()
    {
        if (!auth()->check()) {
            return collect();
        }
        
        return auth()->user()->getTenantPermissions();
    }
}

/**
 * Helper para verificar si un permiso está permitido para el tenant (sin usuario)
 */
if (!function_exists('tenant_allows')) {
    function tenant_allows($permission)
    {
        $tenantId = TenantManager::getCurrentTenantId();
        
        if (!$tenantId) {
            return true;
        }
        
        return app(TenantPermissionFilter::class)
            ->isPermissionAllowedForCurrentTenant($permission);
    }
}

/**
 * Helper para debugging - ver permisos denegados
 */
if (!function_exists('tenant_denied')) {
    function tenant_denied()
    {
        if (!auth()->check()) {
            return [];
        }
        
        return auth()->user()->getDeniedPermissionsByTenant();
    }
}

if (!function_exists('tenant_can_any')) {
    function tenant_can_any(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (tenant_can($permission)) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Obtiene el nombre del plan del tenant actual
 * 
 * @return string|null Nombre del plan ('Lite', 'Lite+', 'Pro') o null si no hay tenant
 */
if (!function_exists('tenant_plan')) {
    function tenant_plan(): ?string
    {
        if (!auth()->check()) {
            return null;
        }

        $user = auth()->user();
        
        // Si no tiene tenant_id, no tiene plan
        if (!$user->tenant_id) {
            return null;
        }

        // Obtener el tenant y su plan
        $tenant = \App\Models\Tenant::find($user->tenant_id);
        
        if (!$tenant || !$tenant->plan_id) {
            return null;
        }

        $plan = \App\Models\Plan::find($tenant->plan_id);
        
        return $plan ? $plan->name : null;
    }
}

/**
 * Verifica si el tenant tiene un plan específico
 * 
 * @param string $planName Nombre del plan a verificar (case-insensitive)
 * @return bool
 */
if (!function_exists('tenant_is_plan')) {
    function tenant_is_plan(string $planName): bool
    {
        $currentPlan = tenant_plan();
        
        if (!$currentPlan) {
            return false;
        }

        return strtolower($currentPlan) === strtolower($planName);
    }
}

/**
 * Verifica si el tenant tiene alguno de los planes especificados
 * 
 * @param array $planNames Array de nombres de planes
 * @return bool
 */
if (!function_exists('tenant_is_any_plan')) {
    function tenant_is_any_plan(array $planNames): bool
    {
        $currentPlan = tenant_plan();
        
        if (!$currentPlan) {
            return false;
        }

        foreach ($planNames as $planName) {
            if (strtolower($currentPlan) === strtolower($planName)) {
                return true;
            }
        }
        
        return false;
    }
}

/**
 * Obtiene el ID del plan del tenant actual
 * 
 * @return int|null
 */
if (!function_exists('tenant_plan_id')) {
    function tenant_plan_id(): ?int
    {
        if (!auth()->check()) {
            return null;
        }

        $user = auth()->user();
        
        if (!$user->tenant_id) {
            return null;
        }

        $tenant = \App\Models\Tenant::find($user->tenant_id);
        
        return $tenant ? $tenant->plan_id : null;
    }
}