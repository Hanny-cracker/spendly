<?php

namespace Tests\Helpers;

use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;

use App\Enums\TransactionType;
use App\Enums\TransactionStatus;

trait CreatesTransactions
{
    protected function createTransaction(

        User $user,
        Account $account,
        ?Category $category = null,
        array $attributes = [],

    ): Transaction {

        $factory = Transaction::factory()
            ->for($user)
            ->for($account);


        if ($category) {
            $factory->for($category);
        }


        return $factory->create(array_merge([

            'amount' => 100,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'title' => 'Test Transaction',
            'date' => now(),
        ], $attributes));

    }
}