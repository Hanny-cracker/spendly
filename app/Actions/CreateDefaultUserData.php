<?php

namespace App\Actions;

use App\Models\User;

class CreateDefaultUserData
{
    public function handle(User $user): void
    {
        foreach (config('spendly.default_categories.expense') as $category) {

            $user->categories()->create([
                'name' => $category,
                'type' => 'expense',
            ]);
        }

        foreach (config('spendly.default_categories.income') as $category) {

            $user->categories()->create([
                'name' => $category,
                'type' => 'income',
            ]);
        }

        foreach (config('spendly.default_accounts') as $account) {

            $user->accounts()->create([
                ...$account,
                'currency' => 'USD',
                'opening_balance' => 0,
                'current_balance' => 0,
            ]);
        }
    }
}
