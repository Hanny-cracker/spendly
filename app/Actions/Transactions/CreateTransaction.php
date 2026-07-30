<?php

namespace App\Actions\Transactions;

use App\Data\Transaction\CreateTransactionData;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;


class CreateTransaction
{

    public function __construct(
        private AccountService $accountService
    ) {}


    public function handle(
        CreateTransactionData $data
    ): Transaction {

        return DB::transaction(function () use ($data) {


            $transaction = Transaction::create([
                'user_id' => $data->userId,
                'account_id' => $data->accountId,
                'category_id' => $data->categoryId,
                'parent_transaction_id' =>
                    $data->parentTransactionId,
                'title' => $data->title,
                'description' => $data->description,
                'amount' => $data->amount,
                'type' => $data->type,
                'date' => $data->date,
                'status' => $data->status,
                'receipt_path' =>
                    $data->receiptPath,
                'notes' => $data->notes,
            ]);


            if ($data->type === TransactionType::Income) {

                $this->accountService
                    ->increaseBalance(
                        $transaction->account,
                        $data->amount
                    );

            }


            if ($data->type === TransactionType::Expense) {

                $this->accountService
                    ->decreaseBalance(
                        $transaction->account,
                        $data->amount
                    );

            }


            return $transaction;

        });

    }

}