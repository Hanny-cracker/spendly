<?php

namespace Tests\Helpers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

trait CreatesTransactions
{
    protected function createTransaction(

        User $user,
        Account $account,
        ?Category $category = null,
        array $attributes = [],

    ): Transaction {

        return Transaction::factory()
            ->create(array_merge([

                'user_id' => $user->id,
                'account_id' => $account->id,
                'category_id' => $category?->id,
                'amount' => 100,
                'type' => TransactionType::Expense,
                'status' => TransactionStatus::Completed,
                'title' => 'Test Transaction',
                'date' => now(),
            ], $attributes));

    }
}
