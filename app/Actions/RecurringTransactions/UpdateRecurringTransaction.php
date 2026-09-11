<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\Data\RecurringTransaction\UpdateRecurringTransactionData;
use App\Enums\RecurringStatus;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactions\RecurringScheduleTime;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateRecurringTransaction
{
    public function __construct(private RecurringScheduleTime $scheduleTime) {}

    public function handle(
        RecurringTransaction $recurringTransaction,
        UpdateRecurringTransactionData $data
    ): RecurringTransaction {

        return DB::transaction(function () use ($recurringTransaction, $data): RecurringTransaction {
            $recurringTransaction = RecurringTransaction::query()->lockForUpdate()->findOrFail($recurringTransaction->id);

            if ($data->userId !== null && $recurringTransaction->user_id !== $data->userId) {
                throw new AuthorizationException;
            }

            if ($data->amount <= 0) {

                throw ValidationException::withMessages([
                    'amount' => 'Amount must be greater than zero.',
                ]);

            }

            if ($data->scheduledTime !== null && ! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $data->scheduledTime)) {
                throw ValidationException::withMessages([
                    'scheduledTime' => 'The scheduled time must use the 24-hour HH:MM format.',
                ]);
            }

            $hasHistory = $recurringTransaction->transactions()->exists();
            if ($hasHistory && $data->type !== $recurringTransaction->type) {
                throw ValidationException::withMessages(['type' => "Transaction type can't be changed after this recurring schedule has generated transactions."]);
            }
            if ($hasHistory && $data->startDate && ! $data->startDate->equalTo($recurringTransaction->start_date)) {
                throw ValidationException::withMessages(['startDate' => "Start date can't be changed after transactions have been generated."]);
            }

            if ($data->userId !== null && ($data->accountId === null || ! Account::query()->whereKey($data->accountId)->where('user_id', $data->userId)->exists())) {
                throw ValidationException::withMessages(['accountId' => 'Invalid account.']);
            }
            if ($data->userId !== null && ($data->categoryId === null || ! Category::query()->whereKey($data->categoryId)->where('user_id', $data->userId)->where('type', $data->type->value)->exists())) {
                throw ValidationException::withMessages(['categoryId' => 'Invalid category for this transaction type.']);
            }

            $startDate = $data->startDate ?? $recurringTransaction->start_date;
            if (
                $data->endDate &&
                $data->endDate->lt(
                    $startDate
                )
            ) {

                throw ValidationException::withMessages([
                    'end_date' => 'End date must be after start date.',
                ]);

            }

            $values = $data->toArray();
            $scheduledTime = $data->scheduledTime ?? substr((string) $recurringTransaction->scheduled_time, 0, 5);
            $timezone = $this->scheduleTime->timezoneForUser($recurringTransaction->user_id);
            $scheduleChanged = $data->frequency !== $recurringTransaction->frequency
                || $scheduledTime !== substr((string) $recurringTransaction->scheduled_time, 0, 5)
                || $timezone !== $recurringTransaction->timezone
                || ($data->startDate && ! $data->startDate->equalTo($recurringTransaction->start_date));

            $values['timezone'] = $timezone;

            if (! $hasHistory) {
                $values['next_run'] = $this->scheduleTime->localToUtc($startDate, $scheduledTime, $timezone);
            } elseif ($scheduleChanged) {
                $nextRun = $this->scheduleTime
                    ->toLocal($recurringTransaction->next_run, $timezone)
                    ->setTimeFromTimeString($scheduledTime)
                    ->utc();
                while ($nextRun->lte(now())) {
                    $nextRun = $this->scheduleTime->nextRunUtc($nextRun, $data->frequency, $timezone);
                }
                $values['next_run'] = $nextRun;
            }
            if ($scheduleChanged) {
                $values['last_24h_notified_at'] = null;
                $values['last_6h_notified_at'] = null;
            }
            if ($recurringTransaction->status->isCompleted() && (! $data->endDate || $data->endDate->gte(today())) && Carbon::parse($values['next_run'] ?? $recurringTransaction->next_run)->gte(today())) {
                $values['status'] = RecurringStatus::Active;
            }
            $recurringTransaction->update($values);

            return $recurringTransaction->refresh();
        });
    }
}
