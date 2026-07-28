<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;


class DeleteTransaction
{

    public function __construct(
        private AccountService $accountService
    ) {}



    public function handle(Transaction $transaction): void
    {

        DB::transaction(function () use ($transaction) {


            if ($transaction->type == 'income') {

                $this->accountService
                    ->decreaseBalance(
                        $transaction->account,
                        $transaction->amount
                    );
            }


            if ($transaction->type == 'expense') {

                $this->accountService
                    ->increaseBalance(
                        $transaction->account,
                        $transaction->amount
                    );
            }


            $transaction->delete($this);
        });
    }
}
