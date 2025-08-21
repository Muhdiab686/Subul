<?php

namespace Database\Factories;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition()
    {
        $user = \App\Models\User::inRandomOrder()->first() ?? \App\Models\User::factory()->create();
        return [
            'user_id' => $user->id,
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'currency' => 'USD',
        ];
    }
}
