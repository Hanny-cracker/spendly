<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Account;

class AccountService
{
    public function adjustBalance(

        Account $account,
        float $amount,
        TransactionType $type,
        bool $reverse = false,
    ): void {

        $signedAmount = $amount * $type->multiplier();

        if ($reverse) {
            $signedAmount *= -1;
        }

        $account->increment(
            'current_balance',
            $signedAmount
        );

        $account->refresh();
    }
}