<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\Data\RecurringTransaction\UpdateRecurringTransactionData;
use App\Models\RecurringTransaction;
use Illuminate\Support\Facades\DB;

class UpdateRecurringTransaction
{
    public function handle(
        RecurringTransaction $recurringTransaction,
        UpdateRecurringTransactionData $data
    ): RecurringTransaction {

        return DB::transaction(function () use (
            $recurringTransaction,
            $data
        ) {


            $recurringTransaction->update(
                array_filter([
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
                    'status' => $data->status,

                ])
            );


            return $recurringTransaction->refresh();

        });

    }
}