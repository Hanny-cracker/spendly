<?php

namespace App\Actions\Transactions;

use App\Data\Transaction\UpdateTransactionData;
use App\Enums\TransactionType;
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
        UpdateTransactionData $data
    ): Transaction {


        return DB::transaction(function () use (
            $transaction,  $data
        ) {
            $this->reverseBalance($transaction);
            $transaction->update([
                'account_id' => $data->accountId,
                'category_id' => $data->categoryId,
                'title' => $data->title,
                'description' => $data->description,
                'amount' => $data->amount,
                'type' => $data->type,
                'date' => $data->date,
                'status' => $data->status,
                'receipt_path' => $data->receiptPath,
                'notes' => $data->notes,
            ]);

            // $transaction->fill([
            //     'account_id' => $data->accountId,
            //     'category_id' => $data->categoryId,
            //     'title' => $data->title,
            //     'description' => $data->description,
            //     'amount' => $data->amount,
            //     'type' => $data->type,
            //     'date' => $data->date,
            //     'status' => $data->status,
            //     'receipt_path' => $data->receiptPath,
            //     'notes' => $data->notes,
            // ]);
            // $transaction->save();
            // $transaction->refresh();

            $this->applyBalance($transaction);

            return $transaction;

        });

    }



    private function reverseBalance(
        Transaction $transaction
    ): void {


        if ($transaction->type === TransactionType::Income) {


            $this->accountService
                ->decreaseBalance(
                    $transaction->account,
                    $transaction->amount
                );

        }


        if ($transaction->type === TransactionType::Expense) {


            $this->accountService
                ->increaseBalance(
                    $transaction->account,
                    $transaction->amount
                );

        }

    }



    private function applyBalance(
        Transaction $transaction
    ): void {


        if ($transaction->type === TransactionType::Income) {


            $this->accountService
                ->increaseBalance(
                    $transaction->account,
                    $transaction->amount
                );

        }


        if ($transaction->type === TransactionType::Expense) {


            $this->accountService
                ->decreaseBalance(
                    $transaction->account,
                    $transaction->amount
                );

        }

    }

}