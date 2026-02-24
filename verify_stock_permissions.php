<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n╔════════════════════════════════════════════╗\n";
echo "║  VERIFICACIÓN DE PERMISOS ACTUALIZADOS    ║\n";
echo "╚════════════════════════════════════════════╝\n\n";

$planes = [
    ['id' => 1, 'nombre' => 'Lite', 'permisos_esperados' => 12],
    ['id' => 2, 'nombre' => 'Lite+', 'permisos_esperados' => 29],
    ['id' => 3, 'nombre' => 'Pro', 'permisos_esperados' => 41],
];

foreach ($planes as $planData) {
    $permisos = DB::table('plan_permissions')
        ->join('permissions', 'plan_permissions.permission_id', '=', 'permissions.id')
        ->where('plan_permissions.plan_id', $planData['id'])
        ->select('permissions.name')
        ->get();
    
    echo "📋 Plan: {$planData['nombre']}\n";
    echo "   Total permisos: " . $permisos->count() . " (esperado: {$planData['permisos_esperados']})\n";
    
    // Verificar show_stocks
    $tieneShowStocks = $permisos->where('name', 'show_stocks')->count() > 0;
    if ($planData['id'] <= 2) {
        echo "   show_stocks: " . ($tieneShowStocks ? '✅ SÍ' : '❌ NO') . "\n";
    }
    
    // Verificar handle_stock
    $tieneHandleStock = $permisos->where('name', 'handle_stock')->count() > 0;
    if ($planData['id'] == 3) {
        echo "   handle_stock: " . ($tieneHandleStock ? '✅ SÍ' : '❌ NO') . "\n";
    }
    
    echo "\n";
}

// Verificar permisos de tenants
echo "╔════════════════════════════════════════════╗\n";
echo "║  VERIFICACIÓN DE TENANTS                   ║\n";
echo "╚════════════════════════════════════════════╝\n\n";

$tenants = DB::table('tenant')->get(['id', 'plan_id']);
foreach ($tenants as $tenant) {
    $planNombre = DB::table('plans')->where('id', $tenant->plan_id)->value('name');
    
    $showStocksId = DB::table('permissions')->where('name', 'show_stocks')->value('id');
    $handleStockId = DB::table('permissions')->where('name', 'handle_stock')->value('id');
    
    $tieneShowStocks = DB::table('tenant_permission_control')
        ->where('tenant_id', $tenant->id)
        ->where('permission_id', $showStocksId)
        ->where('is_allowed', 1)
        ->exists();
    
    $tieneHandleStock = DB::table('tenant_permission_control')
        ->where('tenant_id', $tenant->id)
        ->where('permission_id', $handleStockId)
        ->where('is_allowed', 1)
        ->exists();
    
    echo "Tenant {$tenant->id} (Plan {$planNombre}):\n";
    echo "  show_stocks: " . ($tieneShowStocks ? '✅ PERMITIDO' : '❌ DENEGADO') . "\n";
    echo "  handle_stock: " . ($tieneHandleStock ? '✅ PERMITIDO' : '❌ DENEGADO') . "\n";
    echo "\n";
}

echo "✅ Verificación completada\n\n";
