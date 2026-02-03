<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = [
            'company',
            'administrative',
            'technician',
            'branch',
            'application_area',
            'control_point',
            'contract',
            'customer',
            'device',
            'floorplans',
            'comercial_zones',
            'pest_catalog',
            'product_catalog',
            'order',
            'lead',
            'service_tracking',
            'rotation_plan',
            'directory_management',
            'appearance_settings',
            'quote',
            'invoice',
            'warehouse',
            'tracking',
            'supplier',
            'lot',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreignId('tenant_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('tenant')
                        ->onDelete('cascade');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'company', 'administrative', 'technician', 'branch', 'application_area',
            'control_point', 'contract', 'customer', 'device', 'floorplans',
            'comercial_zones', 'pest_catalog', 'product_catalog', 'order', 'lead',
            'service_tracking', 'rotation_plan', 'directory_management',
            'appearance_settings', 'quote', 'invoice', 'warehouse', 'tracking',
            'supplier', 'lot',
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropConstrainedForeignId('tenant_id');
                });
            }
        }
    }
};