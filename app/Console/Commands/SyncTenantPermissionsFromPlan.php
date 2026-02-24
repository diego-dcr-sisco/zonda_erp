<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\TenantPermissionControl;

class SyncTenantPermissionsFromPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:sync-permissions 
                            {--tenant= : ID del tenant específico a sincronizar}
                            {--all : Sincronizar todos los tenants}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza los permisos de los tenants basándose en sus planes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');
        $syncAll = $this->option('all');

        if (!$tenantId && !$syncAll) {
            $this->error('Debes especificar --tenant=ID o --all');
            return 1;
        }

        if ($tenantId) {
            $this->syncTenant($tenantId);
        } elseif ($syncAll) {
            $this->syncAllTenants();
        }

        return 0;
    }

    /**
     * Sincroniza un tenant específico
     */
    protected function syncTenant($tenantId)
    {
        $tenant = DB::table('tenant')->where('id', $tenantId)->first();

        if (!$tenant) {
            $this->error("Tenant con ID {$tenantId} no encontrado");
            return;
        }

        $this->info("Sincronizando tenant ID: {$tenant->id}");
        
        if (!$tenant->plan_id) {
            $this->warn("  ⚠️  Tenant sin plan asignado, omitiendo...");
            return;
        }

        $this->syncPermissionsForTenant($tenant);
        $this->info("  ✓ Sincronización completada");
    }

    /**
     * Sincroniza todos los tenants
     */
    protected function syncAllTenants()
    {
        $tenants = DB::table('tenant')->whereNotNull('plan_id')->get();
        
        $this->info("Sincronizando {$tenants->count()} tenants...\n");

        $bar = $this->output->createProgressBar($tenants->count());
        $bar->start();

        foreach ($tenants as $tenant) {
            $this->syncPermissionsForTenant($tenant);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ Sincronización completada para todos los tenants");
    }

    /**
     * Lógica de sincronización de permisos
     */
    protected function syncPermissionsForTenant($tenant)
    {
        // Obtener permisos del plan
        $planPermissions = DB::table('plan_permissions')
            ->where('plan_id', $tenant->plan_id)
            ->pluck('permission_id')
            ->toArray();

        // Obtener todos los permisos
        $allPermissions = DB::table('permissions')->pluck('id')->toArray();

        // Eliminar registros antiguos
        DB::table('tenant_permission_control')->where('tenant_id', $tenant->id)->delete();

        // Insertar nuevos registros
        $insertData = [];
        foreach ($allPermissions as $permissionId) {
            $isAllowed = in_array($permissionId, $planPermissions);
            $insertData[] = [
                'tenant_id' => $tenant->id,
                'permission_id' => $permissionId,
                'is_allowed' => $isAllowed,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($insertData)) {
            DB::table('tenant_permission_control')->insert($insertData);
        }
    }
}
