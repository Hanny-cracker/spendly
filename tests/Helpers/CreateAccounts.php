<?php

namespace Tests\Helpers;

use App\Models\User;
use App\Models\Account;
use App\Enums\AccountType;

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