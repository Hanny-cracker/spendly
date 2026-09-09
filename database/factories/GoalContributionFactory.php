<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoalContribution>
 */
class GoalContributionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'goal_id' => Goal::factory(),
            'user_id' => User::factory(),
            'account_id' => null,
            'amount' => 50000,
            'contributed_at' => now(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
