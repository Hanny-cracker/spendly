<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'currency' => 'XAF',
            'timezone' => 'UTC',
            'date_format' => 'd/m/Y',
            'week_starts_on' => 'monday',
            'default_expense_category_id' => null,
            'default_income_category_id' => null,
            'notify_recurring_24h' => true,
            'notify_recurring_6h' => true,
            'notify_recurring_success' => true,
            'notify_recurring_failure' => true,
        ];
    }
}
