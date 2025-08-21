<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixedCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // استخدام ID = 1 كـ created_by_user_id
        $createdByUserId = 1;

        $fixedCosts = [
            [
                'name' => 'tax_amount',
                'value' => 15.00,
                'is_active' => 1,
                'created_by_user_id' => $createdByUserId,
                'updated_by_user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'cost_of_repacking',
                'value' => 25.00,
                'is_active' => 1,
                'created_by_user_id' => $createdByUserId,
                'updated_by_user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'cost_of_is_fragile',
                'value' => 30.00,
                'is_active' => 1,
                'created_by_user_id' => $createdByUserId,
                'updated_by_user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'cost_delivery_origin',
                'value' => 50.00,
                'is_active' => 1,
                'created_by_user_id' => $createdByUserId,
                'updated_by_user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'cost_express_origin',
                'value' => 75.00,
                'is_active' => 1,
                'created_by_user_id' => $createdByUserId,
                'updated_by_user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'cost_customs_origin',
                'value' => 40.00,
                'is_active' => 1,
                'created_by_user_id' => $createdByUserId,
                'updated_by_user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'cost_air_freight',
                'value' => 120.00,
                'is_active' => 1,
                'created_by_user_id' => $createdByUserId,
                'updated_by_user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'cost_delivery_destination',
                'value' => 35.00,
                'is_active' => 1,
                'created_by_user_id' => $createdByUserId,
                'updated_by_user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('fixed_costs')->insert($fixedCosts);
    }
}
