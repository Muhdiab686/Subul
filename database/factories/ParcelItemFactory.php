<?php

namespace Database\Factories;

use App\Models\ParcelItem;
use App\Models\Parcel;
use App\Models\ParcelManage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParcelItemFactory extends Factory
{
    protected $model = ParcelItem::class;

    public function definition()
    {
        // اختر طرد عشوائي
        $parcel = Parcel::inRandomOrder()->first() ?? Parcel::factory()->create();
        // اختر نوع عنصر من قائمة المحتوى المسموح
        $allowedContent = ParcelManage::where('is_allowed', true)->pluck('content')->toArray();
        $itemType = $this->faker->randomElement($allowedContent ?: ['Electronics', 'Clothing', 'Books', 'Toys', 'Accessories']);
        return [
            'parcel_id' => $parcel->id,
            'item_type' => $itemType,
            'quantity' => $this->faker->numberBetween(1, 10),
            'value_per_item' => $this->faker->randomFloat(2, 1, 500),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
