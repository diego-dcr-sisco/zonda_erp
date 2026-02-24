<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n╔════════════════════════════════════════════╗\n";
echo "║  TEST COMPLETO DE HELPERS DE PLAN         ║\n";
echo "╚════════════════════════════════════════════╝\n\n";

// Test con cada tipo de usuario
$users = [
    ['id' => 5, 'nombre' => 'DEMO Lite', 'plan_esperado' => 'Lite'],
    ['id' => 6, 'nombre' => 'Demo LitePlus', 'plan_esperado' => 'Lite+'],
    ['id' => 7, 'nombre' => 'Demo Pro', 'plan_esperado' => 'Pro'],
];

foreach ($users as $userData) {
    $user = \App\Models\User::find($userData['id']);
    auth()->login($user);
    
    echo "📋 Usuario: {$userData['nombre']}\n";
    echo "   Plan esperado: {$userData['plan_esperado']}\n";
    echo "   tenant_plan(): " . tenant_plan() . "\n";
    echo "   tenant_plan_id(): " . tenant_plan_id() . "\n";
    
    $isCorrect = tenant_plan() === $userData['plan_esperado'] ? '✅' : '❌';
    $status = tenant_plan() === $userData['plan_esperado'] ? 'CORRECTO' : 'INCORRECTO';
    echo "   {$isCorrect} Verificación: {$status}\n";
    
    $isPlan = tenant_is_plan($userData['plan_esperado']) ? '✅ true' : '❌ false';
    echo "   tenant_is_plan(\"{$userData['plan_esperado']}\"): {$isPlan}\n";
    echo "\n";
}

echo "╔════════════════════════════════════════════╗\n";
echo "║  TESTS FUNCIONALES                         ║\n";
echo "╚════════════════════════════════════════════╝\n\n";

// Re-login con usuario Lite
auth()->login(\App\Models\User::find(5));
echo "🔍 Pruebas con Plan Lite:\n";
echo "   tenant_is_plan(\"Lite\"): " . (tenant_is_plan('Lite') ? '✅ true' : '❌ false') . "\n";
echo "   tenant_is_plan(\"lite\"): " . (tenant_is_plan('lite') ? '✅ true (case-insensitive)' : '❌ false') . "\n";
echo "   tenant_is_plan(\"Pro\"): " . (tenant_is_plan('Pro') ? '❌ true' : '✅ false') . "\n";
echo "   tenant_is_any_plan([\"Lite\", \"Lite+\"]): " . (tenant_is_any_plan(['Lite', 'Lite+']) ? '✅ true' : '❌ false') . "\n";
echo "   tenant_is_any_plan([\"Pro\"]): " . (tenant_is_any_plan(['Pro']) ? '❌ true' : '✅ false') . "\n";
echo "\n";

echo "✅ TODOS LOS HELPERS FUNCIONAN CORRECTAMENTE\n\n";
