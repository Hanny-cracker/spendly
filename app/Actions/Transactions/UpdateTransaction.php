<?php

namespace App\Actions\Transactions;

use App\Data\Transaction\UpdateTransactionData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\AccountService;
use App\Services\Budgets\BudgetSpendingGuard;
use Illuminate\Support\Facades\DB;

class UpdateTransaction
{
    public function __construct(
        private AccountService $accountService,
        private BudgetSpendingGuard $budgetSpendingGuard,
    ) {}

    public function handle(
        Transaction $transaction,
        UpdateTransactionData $data,
    ): Transaction {

        return DB::transaction(function () use (
            $transaction,
            $data
        ) {
            if ($data->type === TransactionType::Expense && $data->status === TransactionStatus::Completed && $data->transferId === null && $data->categoryId !== null) {
                $this->budgetSpendingGuard->assertCanSpend($transaction->user_id, $data->categoryId, $data->date, $data->amount, $transaction->id);
            }

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

            if ($transaction->status->affectsBalance() && $transaction->type->affectsBalance()) {
                $this->accountService->adjustBalance(
                    account: $oldAccount,
                    amount: $transaction->amount,
                    type: $transaction->type,
                    reverse: true,
                );
            }

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
            $newAccount = Account::query()
                ->where('user_id', $transaction->user_id)
                ->findOrFail($data->accountId);

            /*
            |--------------------------------------------------------------------------
            | Apply new balance
            |--------------------------------------------------------------------------
            */

            if ($data->status->affectsBalance() && $data->type->affectsBalance()) {
                $this->accountService->adjustBalance(
                    account: $newAccount,
                    amount: $data->amount,
                    type: $data->type,
                );
            }

            return $transaction
                ->fresh([
                    'account',
                    'category',
                    'transfer',
                ]);

        });

    }
}
