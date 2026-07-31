<?php

namespace App\Actions\Transactions;

use App\Data\Transaction\CreateTransactionData;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;

class UpdateTransaction
{
    public function __construct(
        private AccountService $accountService,
    ) {
    }

    public function handle(
        Transaction $transaction,
        CreateTransactionData $data,
    ): Transaction {

        return DB::transaction(function () use (
            $transaction,
            $data
        ) {

            /*
            |--------------------------------------------------------------------------
            | Save original account
            |--------------------------------------------------------------------------
            */

            /** @var Account $oldAccount */
            $oldAccount = $transaction
                ->account()
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Reverse previous balance
            |--------------------------------------------------------------------------
            */

            $this->accountService->adjustBalance(

                account: $oldAccount,

                amount: $transaction->amount,

                type: $transaction->type,

                reverse: true,

            );

            /*
            |--------------------------------------------------------------------------
            | Update transaction
            |--------------------------------------------------------------------------
            */

            $transaction->update([

                'account_id' => $data->accountId,

                'category_id' => $data->categoryId,

                'transfer_id' => $data->transferId,

                'title' => $data->title,

                'description' => $data->description,

                'amount' => $data->amount,

                'type' => $data->type,

                'status' => $data->status,

                'date' => $data->date,

                'receipt_path' => $data->receiptPath,

                'notes' => $data->notes,

            ]);

            /*
            |--------------------------------------------------------------------------
            | Load destination account
            |--------------------------------------------------------------------------
            */

            /** @var Account $newAccount */
            $newAccount = Account::findOrFail(
                $data->accountId
            );

            /*
            |--------------------------------------------------------------------------
            | Apply new balance
            |--------------------------------------------------------------------------
            */

            $this->accountService->adjustBalance(

                account: $newAccount,

                amount: $data->amount,

                type: $data->type,

            );

            return $transaction
                ->fresh([
                    'account',
                    'category',
                    'transfer',
                ]);

        });

    }

}