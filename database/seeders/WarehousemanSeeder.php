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
            [
                'first_name' => 'warehouseman',
                'last_name' => 'warehouseman',
                'email' => 'warehouseman@example.com',
                'password' => Hash::make('password'),
                'role' => 'warehouseman',
                'status' => true,
                'address' => 'United Arab Emirates',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'warehouseman2',
                'last_name' => 'warehouseman2',
                'email' => 'warehouseman2@example.com',
                'password' => Hash::make('password'),
                'role' => 'warehouseman',
                'status' => true,
                'address' => 'Turkey',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
