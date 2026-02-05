<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantPermissionControl;
use Spatie\Permission\Models\Permission;

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║         DIAGNÓSTICO: handle_stock en zonda_erp                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";

// 1. Verificar que el permiso existe
echo "\n1️⃣  VERIFICANDO PERMISO handle_stock\n";
echo "   ─" . str_repeat("─", 60) . "\n";

$handleStock = Permission::where('name', 'handle_stock')->first();
if ($handleStock) {
    echo "   ✅ Permiso existe: ID={$handleStock->id}, Categoría={$handleStock->category}\n";
} else {
    echo "   ❌ PERMISO NO EXISTE en tabla permissions\n";
    echo "   Este es el PROBLEMA: El permiso no está registrado\n";
}

// 2. Verificar tenants con Plan Pro
echo "\n2️⃣  VERIFICANDO TENANTS CON PLAN PRO\n";
echo "   ─" . str_repeat("─", 60) . "\n";

$tenantsPro = Tenant::whereIn('plan_id', [2, 3])->with('plan')->get();

if ($tenantsPro->isEmpty()) {
    echo "   ⚠️  No hay tenants con Plan Pro creados\n";
    exit(0);
} else {
    foreach ($tenantsPro as $tenant) {
        echo "\n   Tenant: {$tenant->company_name}\n";
        echo "   ID: {$tenant->id}, Plan: {$tenant->plan->name} (ID: {$tenant->plan_id})\n";
        
        // Verificar si tiene handle_stock en tenant_permission_control
        if ($handleStock) {
            $config = TenantPermissionControl::where('tenant_id', $tenant->id)
                ->where('permission_id', $handleStock->id)
                ->first();
            
            if ($config) {
                $status = $config->is_allowed ? '✅ PERMITIDO' : '❌ DENEGADO';
                echo "   handle_stock en BD: {$status}\n";
            } else {
                echo "   ❌ handle_stock NO CONFIGURADO en tenant_permission_control\n";
            }
        }
        
        // Verificar usuarios del tenant
        $users = User::where('tenant_id', $tenant->id)->get();
        echo "   Usuarios ({$users->count()}):\n";
        
        foreach ($users as $user) {
            echo "\n      └─ {$user->name} (ID: {$user->id})\n";
            
            if ($handleStock) {
                $config = TenantPermissionControl::where('tenant_id', $tenant->id)
                    ->where('permission_id', $handleStock->id)
                    ->first();
                
                if ($config) {
                    $userHasPerm = $user->hasPermissionTo('handle_stock');
                    $status = $userHasPerm ? '✅' : '❌';
                    echo "         Tiene permiso handle_stock: {$status}\n";
                    echo "         tenant_permission_control.is_allowed: " . ($config->is_allowed ? 'true' : 'false') . "\n";
                } else {
                    echo "         ❌ Sin configuración de handle_stock en tenant_permission_control\n";
                }
            }
        }
    }
}

// 3. Resumen
echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    RESUMEN Y SOLUCIÓN                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";

if (!$handleStock) {
    echo "\n❌ PROBLEMA: El permiso handle_stock no existe en la tabla permissions\n";
    echo "\n✅ SOLUCIÓN: Crear el permiso en control_maestro y ejecutar migración\n";
    echo "\n   En control_maestro/zonda_erp:\n";
    echo "   php artisan tinker\n";
    echo "   > Permission::create(['name' => 'handle_stock', 'category' => 't', 'type' => 'w', 'guard_name' => 'web']);\n";
} elseif (!$tenantsPro->isEmpty()) {
    foreach ($tenantsPro as $tenant) {
        if ($handleStock) {
            $config = TenantPermissionControl::where('tenant_id', $tenant->id)
                ->where('permission_id', $handleStock->id)
                ->first();
            
            if (!$config) {
                echo "\n❌ PROBLEMA: El tenant {$tenant->company_name} no tiene handle_stock configurado\n";
                echo "\n✅ SOLUCIÓN: Ejecutar restrictionPermissionsPlan en control_maestro\n";
            } elseif (!$config->is_allowed) {
                echo "\n❌ PROBLEMA: El tenant {$tenant->company_name} tiene handle_stock DENEGADO\n";
                echo "\n✅ SOLUCIÓN: Cambiar is_allowed a true en BD\n";
                echo "   UPDATE tenant_permission_control SET is_allowed = 1\n";
                echo "   WHERE tenant_id = {$tenant->id} AND permission_id = {$handleStock->id};\n";
            }
        }
    }
}

echo "\n";
