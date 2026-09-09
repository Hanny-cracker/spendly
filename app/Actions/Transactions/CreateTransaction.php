<?php

namespace App\Actions\Transactions;

use App\Data\Transaction\CreateTransactionData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Services\AccountService;
use App\Services\Budgets\BudgetSpendingGuard;
use Illuminate\Support\Facades\DB;

class CreateTransaction
{
    public function __construct(
        private AccountService $accountService,
        private BudgetSpendingGuard $budgetSpendingGuard,
    ) {}

    public function handle(
        CreateTransactionData $data
    ): Transaction {

        return DB::transaction(function () use ($data) {
            if ($data->type === TransactionType::Expense && $data->status === TransactionStatus::Completed && $data->transferId === null && $data->categoryId !== null) {
                $this->budgetSpendingGuard->assertCanSpend($data->userId, $data->categoryId, $data->date, $data->amount);
            }

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

            if ($data->status->affectsBalance() && $data->type->affectsBalance()) {
                $this->accountService->adjustBalance(
                    $transaction->account,
                    $transaction->amount,
                    $transaction->type
                );
            }

            return $transaction;
        });
    }
}
