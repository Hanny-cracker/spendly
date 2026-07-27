<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class CreateTransaction
{
    public function handle(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {

            // create transaction
            $transaction = Transaction::create($data);

            // update account balance

            // update budgets

            return $transaction;
        });
        // DB::transaction(function () use ($data) {

        //     // create transaction

        //     // update account balance

        //     // update budgets

        // });
    }
}
