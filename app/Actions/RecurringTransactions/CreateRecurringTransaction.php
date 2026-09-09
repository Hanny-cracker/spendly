<?php

namespace App\Actions\RecurringTransactions;

use App\Data\RecurringTransaction\CreateRecurringTransactionData;
use App\Enums\RecurringStatus;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Services\RecurringTransactions\RecurringTransactionService;

class CreateRecurringTransaction
{
    public function __construct(
        protected RecurringTransactionService $recurringTransactionService,
    ) {}
    public function handle(
        CreateRecurringTransactionData $data
    ): RecurringTransaction {

        if ($data->amount <= 0) {

            throw ValidationException::withMessages([
                'amount' => 'Amount must be greater than zero.',
            ]);
        }

        // Ensure account belongs to user

        $account = Account::query()
            ->whereKey($data->accountId)
            ->where('user_id', $data->userId)
            ->first();

        if (! $account) {

            throw ValidationException::withMessages([
                'account' => 'Invalid account.',
            ]);
        }

        // Validate category ownership

        if ($data->categoryId) {

            $category = Category::query()
                ->whereKey($data->categoryId)
                ->where('user_id', $data->userId)
                ->first();

            if (! $category) {

                throw ValidationException::withMessages([
                    'category' => 'Invalid category.',
                ]);
            }

            if ($category->type->value !== $data->type->value) {
                throw ValidationException::withMessages([
                    'category' => 'The category must match the transaction type.',
                ]);
            }
        }

        $startAt = Carbon::instance($data->startDate)->startOfDay()->setTimeFromTimeString($data->scheduledTime);

        $recurring = RecurringTransaction::create([

            'user_id' => $data->userId,
            'account_id' => $data->accountId,
            'category_id' => $data->categoryId,
            'title' => $data->title,
            'description' => $data->description,
            'amount' => $data->amount,
            'type' => $data->type,
            'frequency' => $data->frequency,
            'interval' => $data->interval,
            'start_date' => $data->startDate,
            'scheduled_time' => $data->scheduledTime,
            'end_date' => $data->endDate,
            'next_run' => $startAt,
            'status' => RecurringStatus::Active,

        ]);

        if ($startAt->lte(now())) {
            $this->recurringTransactionService->generate($recurring);
        }

        return $recurring->refresh();

        // return $recurring;
    }
}
