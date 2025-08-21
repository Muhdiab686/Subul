<?php

namespace Database\Factories;

use App\Models\DeliveryStaff;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryStaffFactory extends Factory
{
    protected $model = DeliveryStaff::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'job_title' => $this->faker->jobTitle(),
        ];
    }
}
