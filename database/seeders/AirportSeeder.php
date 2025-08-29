<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Airport;

class AirportSeeder extends Seeder
{
    public function run()
    {
        Airport::create([
            'code' => 'IST',
            'name' => 'Istanbul Airport',
            'latitude' => 40.9762,
            'longitude' => 28.8146,
        ]);

        Airport::create([
            'code' => 'DXB',
            'name' => 'Dubai Airport',
            'latitude' => 25.2532,
            'longitude' => 55.3657,
        ]);
    }
}
