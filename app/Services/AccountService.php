<?php

namespace App\Services;

use App\Models\Account;

class AccountService
{
    public function increaseBalance(
        Account $account,
        float $amount
    ): void
    {
        $account->balance += $amount;
        $account->save();
    }

    public function decreaseBalance(
        Account $account,
        float $amount
    ): void
    {
        $account->balance -= $amount;
        $account->save();
    }
}
