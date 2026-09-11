<?php

namespace Database\Factories;

use App\Enums\BudgetPeriod;
use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition(): array
    {
        $start = now()->startOfMonth();

        return [

            'public_id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->word().' Budget',
            'amount' => fake()->numberBetween(
                10000,
                500000
            ),
            'period' => BudgetPeriod::Monthly,
            'start_date' => $start,
            'end_date' => $start->copy()->endOfMonth(),
            'alert_percentage' => 80,
            'is_active' => true,

        ];
    }
}
