<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PlanPermissionMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definir los permisos por plan
        $planPermissions = [
            'Lite' => [
                'create_orders',
                'create_pests',
                'create_products',
                'create_services',
                'create_client_users',
                'handle_customers',
                'handle_services',
                'handle_products',
                'handle_pests',
                'handle_orders',
            ],
            'Lite+' => [
                'create_customers',
                'create_leads',
                'create_orders',
                'create_lots',
                'create_pests',
                'create_products',
                'create_quotes',
                'create_services',
                'create_trackings',
                'create_client_users',
                'consult_reports',
                'config_report_appearance',
                'handle_users',
                'handle_customers',
                'handle_branches',
                'handle_comercial_zones',
                'handle_services',
                'handle_products',
                'handle_pests',
                'handle_orders',
                'handle_contracts',
                'handle_leads',
                'handle_quotes',
                'show_client_system',
                'show_crm',
                'show_planning',
                'show_quality_control',
                'show_sedes',
                'show_stocks',
            ],
            'Pro' => [
                'create_branches',
                'create_contracts',
                'create_cpoints',
                'create_customers',
                'create_floorplans',
                'create_lots',
                'create_orders',
                'create_pests',
                'create_products',
                'create_quotes',
                'create_services',
                'create_stocks',
                'create_trackings',
                'create_admin_users',
                'create_client_users',
                'create_leads',
                'consult_reports',
                'consult_dirs',
                'config_report_appearance',
                'handle_drive_files',
                'handle_users',
                'handle_customers',
                'handle_branches',
                'handle_comercial_zones',
                'handle_services',
                'handle_products',
                'handle_pests',
                'handle_orders',
                'handle_contracts',
                'handle_control_points',
                'handle_leads',
                'handle_quotes',
                'handle_stock',
                'show_client_system',
                'show_crm',
                'show_planning',
                'show_quality_control',
                'show_rh',
                'show_sedes',
                'show_stocks',
                'show_invoices',
            ],
        ];

        // Obtener todos los planes
        $plans = Plan::all();

        foreach ($plans as $plan) {
            if (isset($planPermissions[$plan->name])) {
                $permissionNames = $planPermissions[$plan->name];
                
                // Obtener los IDs de los permisos
                $permissions = Permission::whereIn('name', $permissionNames)->get();
                
                if ($permissions->isEmpty()) {
                    $this->command->warn("No se encontraron permisos para el plan {$plan->name}");
                    continue;
                }

                // Eliminar permisos anteriores del plan
                DB::table('plan_permissions')->where('plan_id', $plan->id)->delete();

                // Crear registros en plan_permissions
                $insertData = [];
                foreach ($permissions as $permission) {
                    $insertData[] = [
                        'plan_id' => $plan->id,
                        'permission_id' => $permission->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Insertar en la tabla pivot
                DB::table('plan_permissions')->insert($insertData);
                
                $this->command->info("Asignados " . count($insertData) . " permisos al plan {$plan->name}");
            }
        }

        $this->command->info('Permisos asignados exitosamente a todos los planes.');
    }
}
