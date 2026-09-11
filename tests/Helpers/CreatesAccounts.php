<?php

namespace Tests\Helpers;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\User;

trait CreatesAccounts
{
    protected function createAccount(
        User $user,
        float $balance = 0,
        array $attributes = [],
    ): Account {

        return Account::factory()

            ->for($user)

            ->create(array_merge([
                'name' => 'Cash',
                'type' => AccountType::Cash,
                'currency' => 'USD',
                'opening_balance' => $balance,
                'current_balance' => $balance,
            ], $attributes));

    }
}
