<?php

namespace Database\Factories;

use App\Concerns\ForUser;
use App\Enums\CategoryType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    use ForUser;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->word(),
            'type' => CategoryType::Expense,
            'icon' => 'wallet',
            'color' => '#22C55E',
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => [
            'type' => CategoryType::Income,
            'name' => 'Salary',
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn () => [
            'type' => CategoryType::Expense,
            'name' => 'Food',
        ]);
    }
}
