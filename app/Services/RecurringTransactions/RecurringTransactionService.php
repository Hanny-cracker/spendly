<?php

declare(strict_types=1);

namespace App\Services\RecurringTransactions;

use App\Actions\Transactions\CreateTransaction;
use App\Data\Transaction\CreateTransactionData;
use App\Enums\RecurringStatus;
use App\Enums\TransactionStatus;
use App\Exceptions\RecurringOccurrenceNotDueException;
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

    public function generate(RecurringTransaction $recurringTransaction): Transaction
    {
        [$transaction, $schedule, $scheduledFor] = DB::transaction(function () use ($recurringTransaction): array {
            $schedule = RecurringTransaction::query()->lockForUpdate()->findOrFail($recurringTransaction->id);

            if (! $schedule->shouldGenerate()) {
                throw new RecurringOccurrenceNotDueException('The recurring occurrence is not due.');
            }

            $scheduledFor = Carbon::parse($schedule->next_run);
            $transaction = $this->createTransaction->handle(new CreateTransactionData(
                userId: $schedule->user_id,
                accountId: $schedule->account_id,
                categoryId: $schedule->category_id,
                recurringTransactionId: $schedule->id,
                title: $schedule->title,
                description: $schedule->description,
                amount: (float) $schedule->amount,
                type: $schedule->type,
                date: $scheduledFor->copy(),
                status: TransactionStatus::Completed,
            ));
            $transaction->update(['scheduled_for' => $scheduledFor]);

            $nextRun = $schedule->frequency->nextRun($scheduledFor);
            $completed = $schedule->end_date !== null && $nextRun->startOfDay()->gt($schedule->end_date);
            $schedule->update([
                'next_run' => $nextRun,
                'last_generated_at' => now(),
                'last_24h_notified_at' => null,
                'last_6h_notified_at' => null,
                'status' => $completed ? RecurringStatus::Completed : RecurringStatus::Active,
            ]);

            return [$transaction->refresh(), $schedule->refresh(), $scheduledFor];
        });

        $this->notificationService->generated($schedule, $scheduledFor);

        return $transaction;
    }
}
