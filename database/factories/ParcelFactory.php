<?php

namespace Database\Factories;

use App\Models\Parcel;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParcelFactory extends Factory
{
    protected $model = Parcel::class;

    public function definition()
    {
        $shipment = Shipment::inRandomOrder()->first() ?? Shipment::factory()->create();
        $customerId = $shipment->customer_id;
        return [
            'shipment_id' => $shipment->id,
            'actual_weight' => $this->faker->randomFloat(2, 0.1, 30),
            'special_actual_weight' => $this->faker->optional()->randomFloat(2, 0.1, 10),
            'normal_actual_weight' => $this->faker->optional()->randomFloat(2, 0.1, 10),
            'special_dimensional_weight' => $this->faker->optional()->randomFloat(2, 0.1, 10),
            'normal_dimensional_weight' => $this->faker->optional()->randomFloat(2, 0.1, 10),
            'length' => $this->faker->numberBetween(10, 100),
            'width' => $this->faker->numberBetween(10, 100),
            'height' => $this->faker->numberBetween(10, 100),
            // 'calculated_dimensional_weight' => $this->faker->randomFloat(2, 0.1, 30),
            // 'calculated_final_weight' => $this->faker->randomFloat(2, 0.1, 30),
            'scale_photo_upload' => $this->faker->optional()->imageUrl(),
            'declared_items_count' => $this->faker->numberBetween(1, 10),
            'brand_type' => $this->faker->word(),
            'is_fragile' => $this->faker->boolean(20),
            'needs_repacking' => $this->faker->boolean(10),
            'status' => $this->faker->randomElement(['stored', 'scheduled', 'pickup', 'deliverable']),
            'content_description' => $this->faker->sentence(),
            'notes' => $this->faker->optional()->sentence(),
            'print_notes' => $this->faker->optional()->sentence(),
            'airport_receipt_path' => $this->faker->optional()->imageUrl(),
            'is_opened' => $this->faker->boolean(10),
            'opened_notes' => $this->faker->optional()->sentence(),
            'is_damaged' => $this->faker->boolean(5),
            'damaged_notes' => $this->faker->optional()->sentence(),
            'new_actual_weight' => $this->faker->optional()->randomFloat(2, 0.1, 30),
        ];
    }
}
