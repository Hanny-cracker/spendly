<?php

namespace Database\Factories;

use App\Concerns\ForUser;
use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    use ForUser;
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_id' => Account::factory(),
            'category_id'=>Category::factory(),
            'transfer_id' => null,
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'type' => 'expense',
            'date' => now(),
            'status' => 'completed',
        ];
        
    }


    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
        ]);
    }


    public function expense(): static
    {
        return $this->state(fn () => [
            'type' => 'expense',
        ]);
    }


    public function income(): static
    {
        return $this->state(fn () => [
            'type' => 'income',
        ]);
    }
}