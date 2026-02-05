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