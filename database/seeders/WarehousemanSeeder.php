<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class WarehousemanSeeder extends Seeder
{

    public function run()
    {
        DB::table('users')->insert([
            'first_name' => 'warehouseman',
            'last_name' => 'warehouseman',
            'email' => 'warehouseman@example.com',
            'password' => Hash::make('password'),
            'role' => 'warehouseman',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
