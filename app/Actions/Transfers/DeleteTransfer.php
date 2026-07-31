<?php

namespace App\Actions\Transfers;

use App\Actions\Transactions\DeleteTransaction;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;

class DeleteTransfer
{
    public function __construct(
        private DeleteTransaction $deleteTransaction,
    ) {
    }

    public function handle(
        Transfer $transfer
    ): void {

        DB::transaction(function () use ($transfer) {

            foreach ($transfer->transactions as $transaction) {

                $this->deleteTransaction
                    ->handle($transaction);
            }

            $transfer->delete();

        });

    }
}