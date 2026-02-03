<?php

namespace Database\Seeders;

use App\Models\Administrative;
use App\Models\Technician;
use App\Models\User;
use App\Models\UserContract;
use App\Models\UserFile;
use App\Models\Filenames;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		$files = Filenames::where('type', 'user')->get();
		$user1 = User::create([
			'tenant_id' => NULL,
			'is_superAdmin' => true,
			'name' => 'Diego Domingo Chacon Rivera',
			'nickname' => '@d13g0',
			'username' => 'ddcr',
			'email' => 'admin.ddcr@mail.com',
			'password' => Hash::make('@d13g0'),
			'role_id' => 4,	
			'type_id' => 1,
			'work_department_id' => 1,
			'status_id' => 2,
		]);

		$user2 = User::create([
			'tenant_id' => NULL,
			'is_superAdmin' => true,
			'name' => 'Jorge Antonio Mota Villa',
			'nickname' => '@jmota',
			'username' => 'jmota',
			'email' => 'admin.jmota@mail.com',
			'password' => Hash::make('@jmota'),
			'role_id' => 4,	
			'type_id' => 1,
			'work_department_id' => 1,
			'status_id' => 2,
		]);

		$user3 = User::create([
			'name' => 'Javier Ramos Esqueda',
			'nickname' => '@tecnico1',
			'email' => 'tecnico1@mail.com',
			'password' => Hash::make('@tecnico1'),
			'role_id' => 3,
			'type_id' => 1,
			'work_department_id' => 8,
			'status_id' => 2,
		]);

		$user4 = User::create([
			'name' => 'Jose Maria Torres Huerta',
			'nickname' => '@tecnico2',
			'email' => 'tecnico2@mail.com',
			'password' => Hash::make('@tecnico2'),
			'role_id' => 3,
			'type_id' => 1,
			'work_department_id' => 8,
			'status_id' => 2,
		]);

		UserContract::insert([
			[
				'user_id' => $user1->id,
				'contract_type_id' => 1,
			],
			[
				'user_id' => $user2->id,
				'contract_type_id' => 1,
			],
			[
				'user_id' => $user3->id,
				'contract_type_id' => 1,
			],
			[
				'user_id' => $user4->id,
				'contract_type_id' => 1,
			]
		]);

		foreach ($files as $file) {
			UserFile::insert([
				[
					'user_id' => $user1->id,
					'filename_id' => $file->id,
				],
				[
					'user_id' => $user2->id,
					'filename_id' => $file->id,
				],
				[
					'user_id' => $user3->id,
					'filename_id' => $file->id,
				],
				[
					'user_id' => $user4->id,
					'filename_id' => $file->id,
				]
			]);
		}

		Administrative::insert([
			'user_id' => $user1->id,
			'contract_type_id' => 1,
			'branch_id' => 1,
			'company_id' => 1,
		]);

		Administrative::insert([
			'user_id' => $user2->id,
			'contract_type_id' => 1,
			'branch_id' => 1,
			'company_id' => 1,
		]);

		Technician::insert([
			'user_id' => $user3->id,
			'contract_type_id' => 1,
			'branch_id' => 1,
			'company_id' => 1,
		]);

		Technician::insert([
			'user_id' => $user4->id,
			'contract_type_id' => 1,
			'branch_id' => 1,
			'company_id' => 1,
		]);

		$role = Role::where('simple_role_id', $user1->role_id)->where('work_id', $user1->work_department_id)->first();
		if ($role) {
			$user1->assignRole($role->name);
		}

		// Asignar explícitamente TODOS los permisos al usuario ddcr (útil para superadmin)
		$user1->givePermissionTo(Permission::pluck('name')->toArray());
		$this->command->info("Asignados " . Permission::count() . " permisos al usuario ddcr.");

		$role = Role::where('simple_role_id', $user2->role_id)->where('work_id', $user2->work_department_id)->first();
		if ($role) {
			$user2->assignRole($role->name);
		}
	}
}
