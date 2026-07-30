<?php

namespace Database\Factories;

use App\Concerns\ForUser;
use App\Models\Budget;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    use ForUser;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'user_id' => User::factory(),

            'category_id' => Category::factory(),

            'amount' => 1000,

            'month' => now()->month,

            'year' => now()->year,

        ];
    }
}
