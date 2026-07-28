<?php

namespace App\Actions\Transactions;


use App\Models\Transaction;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;


class UpdateTransaction
{

    public function __construct(
        private AccountService $accountService
    ) {}



    public function handle(
        Transaction $transaction,
        array $data
    ): Transaction {

        return DB::transaction(function () use (
            $transaction,
            $data
        ) {

            $this->reverseBalance($transaction);


            $transaction->update($data);


            $this->applyBalance($transaction);


            return $transaction;
        });
    }



    private function reverseBalance(Transaction $transaction)
    {

        if ($transaction->type === 'income') {

            $this->accountService
                ->decreaseBalance(
                    $transaction->account,
                    $transaction->amount
                );
        }


        if ($transaction->type === 'expense') {

            $this->accountService
                ->increaseBalance(
                    $transaction->account,
                    $transaction->amount
                );
        }
    }



    private function applyBalance(Transaction $transaction)
    {

        if ($transaction->type == 'income') {

            $this->accountService
                ->increaseBalance(
                    $transaction->account,
                    $transaction->amount
                );
        }


        if ($transaction->type == 'expense') {

            $this->accountService
                ->decreaseBalance(
                    $transaction->account,
                    $transaction->amount
                );
        }
    }
}
