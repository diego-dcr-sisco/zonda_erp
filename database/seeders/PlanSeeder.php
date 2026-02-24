<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan1 = Plan::create([
            'name' => 'Lite',
            'limit_users' => 5,   
        ]);

        $plan2 = Plan::create([
            'name' => 'Lite+',
            'limit_users' => 10,    
        ]);

        $plan3 = Plan::create([
            'name' => 'Pro',
            'limit_users' => 20,    
        ]);

    }
}
    