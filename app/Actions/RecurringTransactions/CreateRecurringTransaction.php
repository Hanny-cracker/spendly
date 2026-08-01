<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\Data\RecurringTransaction\CreateRecurringTransactionData;
use App\Enums\RecurringStatus;
use App\Models\RecurringTransaction;
use Illuminate\Support\Facades\DB;

class CreateRecurringTransaction
{
    public function handle(
        CreateRecurringTransactionData $data
    ): RecurringTransaction {

        return DB::transaction(function () use ($data) {

            return RecurringTransaction::create([

                'public_id' => $data->publicId,
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
                'next_run' => $data->nextRun,
                'end_date' => $data->endDate,
                'status' => RecurringStatus::Active,

            ]);

        });
    }
}