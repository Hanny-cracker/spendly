<?php

namespace Database\Factories;

use App\Concerns\ForUser;
use App\Models\User;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    use ForUser;
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement([
                'Cash',
                'Bank',
                'Savings',
            ]),
            'type' => AccountType::Cash,
            'currency' => 'USD',
            'opening_balance' => 1000,
            'current_balance' => 1000,
            'color' => '#3B82F6',
            'is_default' => false,
        ];
    }

    public function cash(): static
    {
        return $this->state(fn() => [
            'type' => AccountType::Cash,
            'name' => 'Cash',
        ]);
    }

    public function bank(): static
    {
        return $this->state(fn() => [
            'type' => AccountType::Bank,
            'name' => 'Bank',
        ]);
    }

    public function default(): static
    {
        return $this->state(fn() => [
            'is_default' => true,
        ]);
    }
}
