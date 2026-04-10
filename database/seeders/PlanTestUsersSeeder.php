<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PlanTestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            'Lite' => 5,
            'Lite+' => 10,
            'Pro' => 20,
            'Starter' => 3,
        ];

        foreach ($plans as $name => $limitUsers) {
            Plan::updateOrCreate(
                ['name' => $name],
                ['limit_users' => $limitUsers]
            );
        }

        $usersByPlan = [
            [
                'plan' => 'Lite',
                'tenant_slug' => 'demo-lite',
                'tenant_name' => 'Demo Lite',
                'user_name' => 'Usuario Lite',
                'nickname' => '@demo_lite',
                'username' => 'demo_lite',
                'email' => 'demo.lite@zonda.local',
            ],
            [
                'plan' => 'Lite+',
                'tenant_slug' => 'demo-lite-plus',
                'tenant_name' => 'Demo Lite Plus',
                'user_name' => 'Usuario Lite Plus',
                'nickname' => '@demo_lite_plus',
                'username' => 'demo_lite_plus',
                'email' => 'demo.liteplus@zonda.local',
            ],
            [
                'plan' => 'Pro',
                'tenant_slug' => 'demo-pro',
                'tenant_name' => 'Demo Pro',
                'user_name' => 'Usuario Pro',
                'nickname' => '@demo_pro',
                'username' => 'demo_pro',
                'email' => 'demo.pro@zonda.local',
            ],
            [
                'plan' => 'Starter',
                'tenant_slug' => 'demo-starter',
                'tenant_name' => 'Demo Starter',
                'user_name' => 'Usuario Starter',
                'nickname' => '@demo_starter',
                'username' => 'demo_starter',
                'email' => 'demo.starter@zonda.local',
            ],
        ];

        $tenantPermissionNames = Permission::where('category', 't')->pluck('name')->toArray();
        $allPermissionIds = Permission::pluck('id')->toArray();
        $adminRole = Role::where('simple_role_id', 4)->where('work_id', 1)->first();

        foreach ($usersByPlan as $row) {
            $plan = Plan::where('name', $row['plan'])->first();

            if (!$plan) {
                $this->command->warn("No se encontro el plan {$row['plan']}, se omite su usuario demo.");
                continue;
            }

            $tenant = Tenant::updateOrCreate(
                ['slug' => $row['tenant_slug']],
                [
                    'company_name' => $row['tenant_name'],
                    'is_active' => true,
                    'plan_id' => $plan->id,
                    'subscription_start' => now(),
                    'subscription_end' => now()->addYear(),
                    'path' => 'tenants/' . $row['tenant_slug'],
                ]
            );

            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'tenant_id' => $tenant->id,
                    'is_superAdmin' => false,
                    'name' => $row['user_name'],
                    'nickname' => $row['nickname'],
                    'username' => $row['username'],
                    'password' => Hash::make('Zonda1234!'),
                    'role_id' => 4,
                    'type_id' => 1,
                    'work_department_id' => 1,
                    'status_id' => 2,
                ]
            );

            if ($adminRole) {
                $user->syncRoles([$adminRole->name]);
            }

            // Se asignan permisos tenant al usuario para que el control final lo determine el plan (tenant_permission_control).
            if (!empty($tenantPermissionNames)) {
                $user->syncPermissions($tenantPermissionNames);
            }

            $planPermissionIds = DB::table('plan_permissions')
                ->where('plan_id', $plan->id)
                ->pluck('permission_id')
                ->toArray();

            DB::table('tenant_permission_control')->where('tenant_id', $tenant->id)->delete();

            $insertRows = [];
            foreach ($allPermissionIds as $permissionId) {
                $insertRows[] = [
                    'tenant_id' => $tenant->id,
                    'permission_id' => $permissionId,
                    'is_allowed' => in_array($permissionId, $planPermissionIds, true),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($insertRows)) {
                DB::table('tenant_permission_control')->insert($insertRows);
            }
        }

        $this->command->info('Seeder PlanTestUsersSeeder ejecutado: 4 usuarios demo creados/actualizados por plan.');
        $this->command->info('Password comun para usuarios demo: Zonda1234!');
    }
}
