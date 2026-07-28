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
        $account->increment(
            'current_balance',
            $amount
        );
    }



    public function decreaseBalance(
        Account $account,
        float $amount
    ): void
    {
        $account->decrement(
            'current_balance',
            $amount
        );
    }



    public function recalculateBalance(
        Account $account
    ): void
    {

        $income = $account
            ->transactions()
            ->where('type','income')
            ->sum('amount');


        $expenses = $account
            ->transactions()
            ->where('type','expense')
            ->sum('amount');


        $account->current_balance =
            $account->opening_balance
            + $income
            - $expenses;

        $account->save();

    }

}