<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use App\Models\TenantPermissionControl;
use App\Tenancy\TenantManager;

class TenantPermissionFilter
{
    /**
     * Obtener solo los permisos permitidos para el tenant actual
     */
    public function getAllowedPermissionsForCurrentTenant()
    {
        $tenantId = TenantManager::getCurrentTenantId();
        
        if (!$tenantId) {
            return collect();
        }

        return DB::table('permissions as p')
            ->join('tenant_permission_control as tpc', 'p.id', '=', 'tpc.permission_id')
            ->where('tpc.tenant_id', $tenantId)
            ->where('tpc.is_allowed', true)
            ->select('p.*')
            ->get()
            ->map(function($item) {
                return Permission::find($item->id);
            });
    }

    /**
     * Obtener IDs de permisos permitidos para el tenant actual
     */
    public function getAllowedPermissionIdsForCurrentTenant()
    {
        $tenantId = TenantManager::getCurrentTenantId();
        
        if (!$tenantId) {
            return [];
        }

        return DB::table('tenant_permission_control')
            ->where('tenant_id', $tenantId)
            ->where('is_allowed', true)
            ->pluck('permission_id')
            ->toArray();
    }

    /**
     * Verificar si un permiso está permitido para el tenant actual
     */
    public function isPermissionAllowedForCurrentTenant($permissionName)
    {
        $tenantId = TenantManager::getCurrentTenantId();
        
        if (!$tenantId) {
            return false;
        }

        return DB::table('tenant_permission_control as tpc')
            ->join('permissions as p', 'tpc.permission_id', '=', 'p.id')
            ->where('tpc.tenant_id', $tenantId)
            ->where('tpc.is_allowed', true)
            ->where('p.name', $permissionName)
            ->exists();
    }

    /**
     * Filtrar array de permisos - solo los permitidos para el tenant
     */
    public function filterAllowedPermissions($permissions)
    {
        $allowedPermissionIds = $this->getAllowedPermissionIdsForCurrentTenant();
        return array_filter($permissions, function($permission) use ($allowedPermissionIds) {
            if ($permission instanceof Permission) {
                return in_array($permission->id, $allowedPermissionIds);
            }
            
            // Si es string, buscar el permiso
            $permissionModel = Permission::where('name', $permission)->first();
            return $permissionModel && in_array($permissionModel->id, $allowedPermissionIds);
        });
    }

    /**
     * Obtener permisos denegados para el tenant actual
     */
    public function getDeniedPermissionsForCurrentTenant()
    {
        $tenantId = TenantManager::getCurrentTenantId();
        
        if (!$tenantId) {
            return collect();
        }

        return DB::table('permissions as p')
            ->join('tenant_permission_control as tpc', 'p.id', '=', 'tpc.permission_id')
            ->where('tpc.tenant_id', $tenantId)
            ->where('tpc.is_allowed', false)
            ->pluck('p.name')
            ->toArray();
        
    }

    /**
     * Verificar si el tenant tiene algún permiso denegado
     */
    public function hasDeniedPermissions()
    {
        $tenantId = TenantManager::getCurrentTenantId();
        
        if (!$tenantId) {
            return false;
        }

        return DB::table('tenant_permission_control')
            ->where('tenant_id', $tenantId)
            ->where('is_allowed', false)
            ->exists();
    }
}