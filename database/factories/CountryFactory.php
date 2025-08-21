<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition()
    {
        // قائمة الدول المطلوبة مع أسمائها ورموز الاتصال الدولية
        $countries = [
            [
                'name' => 'Turkey',
                'code' => '+90'
            ],
            [
                'name' => 'United Arab Emirates',
                'code' => '+971'
            ]
        ];

        // اختيار دولة عشوائية من القائمة
        $country = $this->faker->randomElement($countries);

        return [
            'name' => $country['name'],
            'code' => $country['code'],
            'is_enabled' => 1,
        ];
    }
}
