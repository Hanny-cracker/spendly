<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Exceptions\InsufficientAccountBalanceException;
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

        $lockedAccount = Account::query()->lockForUpdate()->findOrFail($account->id);
        if (! $reverse && $type->isExpense() && (float) $lockedAccount->current_balance < $amount) {
            throw new InsufficientAccountBalanceException((float) $lockedAccount->current_balance, $amount);
        }

        $lockedAccount->increment('current_balance', $signedAmount);
        $account->setRawAttributes($lockedAccount->getAttributes());
    }
}
