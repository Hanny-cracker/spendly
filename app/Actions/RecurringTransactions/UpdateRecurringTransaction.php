<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\Data\RecurringTransaction\UpdateRecurringTransactionData;
use App\Models\RecurringTransaction;
use Illuminate\Validation\ValidationException;

class UpdateRecurringTransaction
{

    public function handle(
        RecurringTransaction $recurringTransaction,
        UpdateRecurringTransactionData $data
    ): RecurringTransaction {


        if ($data->amount <= 0) {

            throw ValidationException::withMessages([
                'amount' => 'Amount must be greater than zero.',
            ]);

        }


        if (
            $data->endDate &&
            $data->endDate->lt(
                $recurringTransaction->start_date
            )
        ) {

            throw ValidationException::withMessages([
                'end_date' => 'End date must be after start date.',
            ]);

        }



        $recurringTransaction->update(
            $data->toArray()
        );


        return $recurringTransaction;

    }

}