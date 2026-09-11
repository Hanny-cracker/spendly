<?php

namespace Database\Factories;

use App\Concerns\ForUser;
use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringTransaction>
 */
class RecurringTransactionFactory extends Factory
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
            'account_id' => Account::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->words(2, true),
            'amount' => 150,
            'type' => TransactionType::Expense,
            'frequency' => RecurringFrequency::Monthly,
            'interval' => 1,
            'start_date' => today(),
            'scheduled_time' => '08:00',
            'next_run' => now()->subMinute(),
            'status' => RecurringStatus::Active,
        ];
    }
}
