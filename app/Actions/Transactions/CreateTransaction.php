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
                'transfer_id' => $data->transferId,
                'recurring_transaction_id' => $data->recurringTransactionId,
                'title' => $data->title,
                'description' => $data->description,
                'amount' => $data->amount,
                'type' => $data->type,
                'status' => $data->status,
                'date' => $data->date,
                'receipt_path' => $data->receiptPath,
                'notes' => $data->notes,
            ]);

            if ($data->type->isIncome()) {

                $this->accountService
                    ->adjustBalance(
                        $transaction->account,
                        $transaction->amount,
                        $transaction->type
                    );
            }

            if ($data->type->isExpense()) {

                $this->accountService
                    ->adjustBalance(
                        $transaction->account,
                        $transaction->amount,
                        $transaction->type
                    );
            }

            return $transaction;
        });
    }
}
