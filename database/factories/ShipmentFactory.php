<?php

namespace Database\Factories;

use App\Models\Shipment;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShipmentFactory extends Factory
{
    protected $model = Shipment::class;

    public function definition()
    {
        $customer = User::whereIn('role', ['customer', 'company'])->inRandomOrder()->first() ?? User::factory()->create();
        return [
            'tracking_number' => $this->faker->unique()->uuid(),
            'type' => $this->faker->randomElement(['ship_pay', 'ship_only', 'pay_only']),
            'customer_id' => $customer->id,
            'supplier_id' => Supplier::inRandomOrder()->first()?->id ?? Supplier::factory(),
            'origin_country_id' => Country::inRandomOrder()->first()?->id ?? Country::factory(),
            'destination_country_id' => Country::inRandomOrder()->first()?->id ?? Country::factory(),
            'status' => $this->faker->optional(0.7)->randomElement(['in_process', 'in_the_way', 'delivered', 'rejected']),
            'declared_parcels_count' => $this->faker->numberBetween(1, 5),
            'actual_parcels_count' => $this->faker->numberBetween(1, 5),
            'sent_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'delivered_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
             'warehouse_received_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'warehouse_receiver_id' => User::factory(),
            'cancellation_reason' => $this->faker->optional()->sentence(),
            'notes' => $this->faker->optional()->sentence(),
            'created_by_user_id' => $customer->id,
            'is_approved' => $this->faker->boolean(80),
            'mark_as_delivered' => $this->faker->boolean(70),
            'delivered_to_WH_dis' => $this->faker->boolean(60),
        ];
    }
}
