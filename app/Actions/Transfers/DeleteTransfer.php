<?php

namespace App\Actions\Transfers;

use App\Actions\Transactions\DeleteTransaction;
use App\Models\Transaction;
use App\Models\Transfer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DeleteTransfer
{
    public function __construct(
        private DeleteTransaction $deleteTransaction,
    ) {}

    public function handle(
        Transfer $transfer
    ): void {

        DB::transaction(function () use ($transfer) {

            /** @var Collection<int, Transaction> $transactions */
            $transactions = $transfer->transactions;

            foreach ($transactions as $transaction) {

                $this->deleteTransaction
                    ->handle($transaction, allowTransfer: true);
            }

            $transfer->delete();

        });

    }
}
