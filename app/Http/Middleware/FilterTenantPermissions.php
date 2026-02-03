<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantPermissionFilter;

class FilterTenantPermissions
{
    protected $permissionFilter;

    public function __construct(TenantPermissionFilter $permissionFilter)
    {
        $this->permissionFilter = $permissionFilter;
    }

    public function handle(Request $request, Closure $next)
    {
        // Interceptar las consultas de permisos de Spatie
        $this->interceptPermissionQueries();
        
        return $next($request);
    }

    protected function interceptPermissionQueries()
    {
        // Interceptar la relación 'permissions' del modelo User
        \App\Models\User::resolveRelationUsing('filteredPermissions', function($userModel) {
            return $userModel->belongsToMany(
                \Spatie\Permission\Models\Permission::class,
                'model_has_permissions',
                'model_id',
                'permission_id'
            )->where(function($query) {
                // Filtrar solo permisos permitidos para el tenant
                $allowedPermissionIds = app(TenantPermissionFilter::class)
                    ->getAllowedPermissionIdsForCurrentTenant();
                
                $query->whereIn('permissions.id', $allowedPermissionIds);
            });
        });

        // Interceptar la relación 'roles.permissions'
        \Spatie\Permission\Models\Role::resolveRelationUsing('filteredPermissions', function($roleModel) {
            return $roleModel->belongsToMany(
                \Spatie\Permission\Models\Permission::class,
                'role_has_permissions',
                'role_id',
                'permission_id'
            )->where(function($query) {
                // Filtrar solo permisos permitidos para el tenant
                $allowedPermissionIds = app(TenantPermissionFilter::class)
                    ->getAllowedPermissionIdsForCurrentTenant();
                
                $query->whereIn('permissions.id', $allowedPermissionIds);
            });
        });
    }
}