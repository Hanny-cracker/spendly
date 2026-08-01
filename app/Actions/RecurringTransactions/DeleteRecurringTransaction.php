<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\Models\RecurringTransaction;
use Illuminate\Support\Facades\DB;

class DeleteRecurringTransaction
{
    public function handle(
        RecurringTransaction $recurringTransaction
    ): bool {

        return DB::transaction(function () use ($recurringTransaction) {

            return $recurringTransaction->delete();

        });
    }
}