<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\Models\RecurringTransaction;
use App\Enums\RecurringStatus;
use Illuminate\Support\Facades\DB;

class PauseRecurringTransaction
{

    public function handle(
        RecurringTransaction $recurringTransaction
    ): RecurringTransaction {


        return DB::transaction(function () use ($recurringTransaction) {


            $recurringTransaction->update([
                'status'=>RecurringStatus::Paused,
            ]);


            return $recurringTransaction->refresh();

        });

    }
}