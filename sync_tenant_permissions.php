<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== SINCRONIZACIÓN DE PERMISOS POR TENANT ===\n\n";

// Obtener todos los tenants
$tenants = DB::table('tenant')->get(['id', 'plan_id']);

foreach ($tenants as $tenant) {
    echo "Tenant ID: {$tenant->id} - Plan ID: {$tenant->plan_id}\n";
    
    // Obtener permisos del plan
    $planPermissions = DB::table('plan_permissions')
        ->where('plan_id', $tenant->plan_id)
        ->pluck('permission_id')
        ->toArray();
    
    echo "  Permisos del plan: " . count($planPermissions) . "\n";
    
    // Obtener todos los permisos del sistema
    $allPermissions = DB::table('permissions')->pluck('id')->toArray();
    
    // Eliminar registros antiguos
    DB::table('tenant_permission_control')->where('tenant_id', $tenant->id)->delete();
    
    // Insertar nuevos registros
    foreach ($allPermissions as $permId) {
        $isAllowed = in_array($permId, $planPermissions);
        DB::table('tenant_permission_control')->insert([
            'tenant_id' => $tenant->id,
            'permission_id' => $permId,
            'is_allowed' => $isAllowed,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    echo "  ✓ Sincronizado\n\n";
}

// Verificar handle_customers para cada tenant
echo "=== VERIFICACIÓN ===\n\n";
$handleCustomersId = DB::table('permissions')->where('name', 'handle_customers')->value('id');

foreach ($tenants as $tenant) {
    $control = DB::table('tenant_permission_control')
        ->where('tenant_id', $tenant->id)
        ->where('permission_id', $handleCustomersId)
        ->first();
    
    $status = $control && $control->is_allowed ? '✓ PERMITIDO' : '✗ DENEGADO';
    echo "Tenant {$tenant->id}: handle_customers {$status}\n";
}

echo "\n=== SINCRONIZACIÓN COMPLETADA ===\n\n";
