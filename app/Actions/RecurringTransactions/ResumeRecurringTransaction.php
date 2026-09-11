<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\Enums\RecurringStatus;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactions\RecurringScheduleTime;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResumeRecurringTransaction
{
    public function __construct(private RecurringScheduleTime $scheduleTime) {}

    public function handle(RecurringTransaction $recurringTransaction, ?int $userId = null): RecurringTransaction
    {
        return DB::transaction(function () use ($recurringTransaction, $userId): RecurringTransaction {
            $recurringTransaction = RecurringTransaction::query()->lockForUpdate()->findOrFail($recurringTransaction->id);
            if ($userId !== null && $recurringTransaction->user_id !== $userId) {
                throw new AuthorizationException;
            }
            if ($recurringTransaction->status->isCompleted()) {
                throw ValidationException::withMessages(['status' => 'A completed recurring schedule cannot be resumed.']);
            }

            $nextRun = $recurringTransaction->next_run->copy()->utc();
            while ($nextRun->lte(now())) {
                $nextRun = $this->scheduleTime->nextRunUtc(
                    $nextRun,
                    $recurringTransaction->frequency,
                    $recurringTransaction->timezone,
                );
            }
            $recurringTransaction->update(['status' => RecurringStatus::Active, 'next_run' => $nextRun, 'last_24h_notified_at' => null, 'last_6h_notified_at' => null]);

            return $recurringTransaction->refresh();
        });
    }
}
