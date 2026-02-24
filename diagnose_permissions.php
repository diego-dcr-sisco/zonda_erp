<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== DIAGNÓSTICO DE PERMISOS ZONDA_ERP ===\n\n";

echo "Base de datos: " . DB::connection()->getDatabaseName() . "\n\n";

// 1. Verificar permisos en la tabla permissions
echo "1. Permisos en sistema:\n";
$permissions = DB::table('permissions')->get(['id', 'name']);
echo "Total de permisos: " . $permissions->count() . "\n";
$handleCustomers = $permissions->where('name', 'handle_customers')->first();
if ($handleCustomers) {
    echo "✓ Permiso 'handle_customers' existe (ID: {$handleCustomers->id})\n";
} else {
    echo "✗ Permiso 'handle_customers' NO existe\n";
}
echo "\n";

// 2. Verificar usuarios
echo "2. Usuarios en el sistema:\n";
$users = DB::table('user')->get(['id', 'name', 'username', 'tenant_id', 'is_superAdmin']);
echo "Total usuarios: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "  Usuario: {$user->name} (@{$user->username}) [ID: {$user->id}]\n";
    echo "    Tenant ID: " . ($user->tenant_id ?? 'Sin tenant') . "\n";
    echo "    Super Admin: " . ($user->is_superAdmin ? 'Sí' : 'No') . "\n";
    
    // Permisos del usuario vía Spatie (directos)
    $userPermissions = DB::table('model_has_permissions')
        ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
        ->where('model_has_permissions.model_type', 'App\\Models\\User')
        ->where('model_has_permissions.model_id', $user->id)
        ->get(['permissions.name']);
    
    // Roles del usuario
    $userRoles = DB::table('model_has_roles')
        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->where('model_has_roles.model_type', 'App\\Models\\User')
        ->where('model_has_roles.model_id', $user->id)
        ->get(['roles.id', 'roles.name']);
    
    echo "    Roles: ";
    if ($userRoles->count() > 0) {
        echo $userRoles->pluck('name')->implode(', ') . "\n";
    } else {
        echo "Sin roles asignados\n";
    }
    
    echo "    Permisos directos: " . $userPermissions->count() . "\n";
    
    if ($userPermissions->count() > 0) {
        $hasHandleCustomers = $userPermissions->where('name', 'handle_customers')->count() > 0;
        if ($hasHandleCustomers) {
            echo "      ✓ Tiene 'handle_customers' (directo)\n";
        }
    }
    
    // Permisos vía roles
    if ($userRoles->count() > 0) {
        foreach ($userRoles as $role) {
            $rolePermissions = DB::table('role_has_permissions')
                ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                ->where('role_has_permissions.role_id', $role->id)
                ->get(['permissions.name']);
            
            echo "    Permisos del rol '{$role->name}': " . $rolePermissions->count() . "\n";
            
            if ($rolePermissions->count() > 0) {
                $hasHandleCustomers = $rolePermissions->where('name', 'handle_customers')->count() > 0;
                if ($hasHandleCustomers) {
                    echo "      ✓ Tiene 'handle_customers' vía rol '{$role->name}'\n";
                } else {
                    echo "      ✗ NO tiene 'handle_customers' vía rol '{$role->name}'\n";
                }
            }
        }
    }
    
    echo "\n";
}

// 3. Verificar roles
echo "3. Roles en el sistema:\n";
$roles = DB::table('roles')->get(['id', 'name']);
echo "Total roles: " . $roles->count() . "\n\n";

foreach ($roles as $role) {
    echo "  Rol: {$role->name} (ID: {$role->id})\n";
    
    $rolePermissions = DB::table('role_has_permissions')
        ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
        ->where('role_has_permissions.role_id', $role->id)
        ->get(['permissions.name']);
    
    echo "    Permisos: " . $rolePermissions->count() . "\n";
    
    if ($rolePermissions->count() > 0) {
        $hasHandleCustomers = $rolePermissions->where('name', 'handle_customers')->count() > 0;
        if ($hasHandleCustomers) {
            echo "      ✓ Tiene 'handle_customers'\n";
        } else {
            echo "      ✗ NO tiene 'handle_customers'\n";
        }
    }
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n\n";
