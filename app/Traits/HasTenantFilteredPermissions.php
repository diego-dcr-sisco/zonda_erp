<?php

namespace App\Traits;

use App\Services\TenantPermissionFilter;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Log;


trait HasTenantFilteredPermissions
{
    /**
     * Verificar permiso considerando tenant
     */
    public function hasTenantPermission($permission, $guardName = null): bool
    {
        Log::info('Verificando permiso para tenant', [
            'usuario_id' => $this->id,
            'permission' => $permission,
            'guard_name' => $guardName,
        ]);
        $permissionFilter = app(TenantPermissionFilter::class);
        
        
        // Si no hay tenant activo, usar comportamiento normal de Spatie
        if (!TenantManager::getCurrentTenantId()) {
            return $this->hasPermissionTo($permission, $guardName);
        }
        
        // Primero verificar si el permiso está permitido para el tenant
        $permissionName = $this->getPermissionName($permission, $guardName);
        
        if ($permissionName && !$permissionFilter->isPermissionAllowedForCurrentTenant($permissionName)) {
            return false;
        }
        
        // Si está permitido, usar la lógica normal de Spatie
        return $this->hasPermissionTo($permission, $guardName);
    }

    /**
     * Obtener el nombre del permiso desde diferentes formatos
     */
    protected function getPermissionName($permission, $guardName = null)
    {
        if (is_string($permission)) {
            return $permission;
        }
        
        if (is_int($permission)) {
            $permissionModel = \Spatie\Permission\Models\Permission::findById($permission, $guardName);
            return $permissionModel ? $permissionModel->name : null;
        }
        
        if ($permission instanceof \Spatie\Permission\Contracts\Permission) {
            return $permission->name;
        }
        
        return null;
    }

    /**
     * Obtener solo los permisos permitidos para el tenant
     */
    public function getTenantPermissions(): \Illuminate\Support\Collection
    {
        $allPermissions = $this->getAllPermissions();
        
        // Si no hay tenant activo, retornar todos
        if (!TenantManager::getCurrentTenantId()) {
            return $allPermissions;
        }
        
        // Filtrar solo los permitidos para el tenant
        $permissionFilter = app(TenantPermissionFilter::class);
        
        return $allPermissions->filter(function($permission) use ($permissionFilter) {
            return $permissionFilter->isPermissionAllowedForCurrentTenant($permission->name);
        });
    }

    /**
     * Obtener nombres de permisos permitidos para el tenant
     */
    public function getTenantPermissionNames(): array
    {
        return $this->getTenantPermissions()->pluck('name')->toArray();
    }

    /**
     * Verificar si algún permiso está denegado por tenant
     */
    public function hasDeniedPermissionsByTenant(): bool
    {
        if (!TenantManager::getCurrentTenantId()) {
            return false;
        }
        
        $allPermissions = $this->getAllPermissions();
        $allowedPermissions = $this->getTenantPermissions();
        
        return $allPermissions->count() > $allowedPermissions->count();
    }

    /**
     * Obtener lista de permisos denegados por tenant
     */
    public function getDeniedPermissionsByTenant(): array
    {
        if (!TenantManager::getCurrentTenantId()) {
            return [];
        }
        
        $allPermissions = $this->getAllPermissions()->pluck('name')->toArray();
        $allowedPermissions = $this->getTenantPermissions()->pluck('name')->toArray();
        
        return array_diff($allPermissions, $allowedPermissions);
    }

    /**
     * Verificar múltiples permisos (como hasAnyPermission de Spatie pero con tenant)
     */
    public function hasAnyTenantPermission(...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasTenantPermission($permission)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Verificar todos los permisos (como hasAllPermissions de Spatie pero con tenant)
     */
    public function hasAllTenantPermissions(...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasTenantPermission($permission)) {
                return false;
            }
        }
        
        return true;
    }
}