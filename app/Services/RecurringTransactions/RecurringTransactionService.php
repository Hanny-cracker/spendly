<?php

declare(strict_types=1);

namespace App\Services\RecurringTransactions;

use App\Services\RecurringTransactions\RecurringTransactionNotificationService;
use App\Actions\Transactions\CreateTransaction;
use App\Data\Transaction\CreateTransactionData;
use App\Enums\RecurringStatus;
use App\Enums\TransactionStatus;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringTransactionService
{
    public function __construct(
        protected CreateTransaction $createTransaction,
        protected RecurringTransactionNotificationService $notificationService,
    ) {}

    public function generate(
        RecurringTransaction $recurringTransaction
    ): Transaction {

        return DB::transaction(function () use ($recurringTransaction) {

            /*
             |--------------------------------------------------------------------------
             | Create transaction
             |--------------------------------------------------------------------------
             */

            $transaction = $this->createTransaction->handle(
                new CreateTransactionData(
                    userId: $recurringTransaction->user_id,
                    accountId: $recurringTransaction->account_id,
                    categoryId: $recurringTransaction->category_id,
                    recurringTransactionId: $recurringTransaction->id,
                    title: $recurringTransaction->title,
                    description: $recurringTransaction->description,
                    amount: (float) $recurringTransaction->amount,
                    type: $recurringTransaction->type,
                    date: Carbon::instance(now()),
                    status: TransactionStatus::Completed,
                )
            );

            /*
             |--------------------------------------------------------------------------
             | Calculate next run
             |--------------------------------------------------------------------------
             */

            $this->updateNextRun(
                $recurringTransaction
            );

            /*
             |--------------------------------------------------------------------------
             | Notify user
             |--------------------------------------------------------------------------
             */

            $this->notificationService->generated(
                $recurringTransaction
            );

            return $transaction;
        });
    }

    private function updateNextRun(
        RecurringTransaction $recurring
    ): void {

        $nextRun = $recurring->frequency->nextRun(
            Carbon::parse($recurring->next_run)
        );

        $recurring->update([
            'next_run' => $nextRun,
            'last_generated_at' => now(),

            /*
             * Reset notification tracking for
             * the next occurrence.
             */
            'last_24h_notified_at' => null,
            'last_6h_notified_at' => null,
        ]);

        if (
            $recurring->end_date
            &&
            $nextRun->greaterThan(
                $recurring->end_date
            )
        ) {
            $recurring->update([
                'status' => RecurringStatus::Completed,
            ]);
        }
    }
}