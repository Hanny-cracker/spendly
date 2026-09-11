<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RecurringStatus;
use App\Exceptions\BudgetExceededException;
use App\Exceptions\RecurringOccurrenceNotDueException;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactions\RecurringTransactionNotificationService;
use App\Services\RecurringTransactions\RecurringTransactionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateRecurringTransactions extends Command
{
    protected $signature = 'transactions:generate-recurring';

    protected $description = 'Generate due recurring transactions';

    public function handle(RecurringTransactionService $service, RecurringTransactionNotificationService $notifications): int
    {
        $recurringTransactions = RecurringTransaction::query()
            ->where('status', RecurringStatus::Active)
            ->where('next_run', '<=', now())
            ->orderBy('next_run')
            ->get();

        foreach ($recurringTransactions as $recurring) {
            $scheduledFor = $recurring->next_run->copy();

            try {
                $transaction = $service->generate($recurring);
                Log::info('Recurring occurrence generated.', ['recurring_transaction_id' => $recurring->id, 'public_id' => $recurring->public_id, 'scheduled_for' => $scheduledFor->toDateTimeString(), 'transaction_id' => $transaction->id]);
                $this->info("Generated: {$recurring->title}");
            } catch (BudgetExceededException $exception) {
                DB::transaction(function () use ($recurring, $scheduledFor, $exception, $notifications): void {
                    $locked = RecurringTransaction::query()->lockForUpdate()->findOrFail($recurring->id);
                    if ($locked->last_failure_notified_for?->equalTo($scheduledFor)) {
                        return;
                    }

                    $locked->update(['last_failure_notified_for' => $scheduledFor]);
                    DB::afterCommit(fn () => $notifications->budgetFailure($locked->fresh(), $scheduledFor, $exception->availability));
                });
                Log::warning('Recurring occurrence blocked by budget.', ['recurring_transaction_id' => $recurring->id, 'public_id' => $recurring->public_id, 'scheduled_for' => $scheduledFor->toDateTimeString(), 'failure' => 'insufficient_budget']);
                $this->warn("Skipped {$recurring->title}: {$exception->getMessage()}");
            } catch (RecurringOccurrenceNotDueException) {
                Log::notice('Recurring occurrence was already processed or is no longer due.', ['recurring_transaction_id' => $recurring->id, 'public_id' => $recurring->public_id, 'scheduled_for' => $scheduledFor->toDateTimeString()]);
            } catch (Throwable $exception) {
                Log::error('Recurring occurrence generation failed.', ['recurring_transaction_id' => $recurring->id, 'public_id' => $recurring->public_id, 'scheduled_for' => $scheduledFor->toDateTimeString(), 'exception' => $exception]);
                report($exception);
                $this->error("Failed: {$recurring->title}");
            }
        }

        return self::SUCCESS;
    }
}
