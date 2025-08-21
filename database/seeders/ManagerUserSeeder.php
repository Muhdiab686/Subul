<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ManagerUserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'first_name' => 'manager',
            'last_name' => 'manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
