<?php

namespace App\Actions;

use App\Models\User;

// This file is used for onboarding / initial setup of a user’s financial data.
// Why it exists:
// To automatically populate a new user’s account with starter data.
// It saves the app from requiring the user to manually create basic categories and accounts first.
// It keeps defaults centralized in spendly.php.
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
