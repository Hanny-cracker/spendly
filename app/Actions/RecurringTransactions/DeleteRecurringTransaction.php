<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\Models\RecurringTransaction;
use Exception;

class DeleteRecurringTransaction
{

    public function handle(
        RecurringTransaction $recurringTransaction
    ): bool {


        if(
            $recurringTransaction->status->isActive()
        ){

            throw new Exception(
                'Pause recurring transaction before deleting.'
            );

        }


        return (bool) $recurringTransaction->delete();

    }

}