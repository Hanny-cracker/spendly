<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;


class CreateTransaction
{

    public function __construct(
        private AccountService $accountService
    ) {}



    public function handle(array $data): Transaction
    {

        return DB::transaction(function () use ($data) {

            $transaction = Transaction::create($data);


            if ($transaction->type === 'income') {

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


            return $transaction;
        });
    }
}
