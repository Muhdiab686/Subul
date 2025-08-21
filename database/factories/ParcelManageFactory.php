<?php

namespace Database\Factories;

use App\Models\ParcelManage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParcelManageFactory extends Factory
{
    protected $model = ParcelManage::class;

    public function definition()
    {
        $contentTypes = [
            'Electronics',
            'Clothing',
            'Books',
            'Toys',
            'Accessories',
            'Home & Garden',
            'Sports Equipment',
            'Beauty Products',
            'Automotive Parts',
            'Tools & Hardware',
            'Food & Beverages',
            'Health & Medical',
            'Jewelry',
            'Watches',
            'Shoes',
            'Bags & Luggage',
            'Furniture',
            'Art & Collectibles',
            'Musical Instruments',
            'Pet Supplies'
        ];

        return [
            'content' => $this->faker->randomElement($contentTypes),
            'is_allowed' => $this->faker->boolean(80),
        ];
    }
}
