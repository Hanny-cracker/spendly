<?php

namespace App\Services;

use App\Models\RecurringTransaction;
use App\Actions\Transactions\CreateTransaction;


class RecurringTransactionService
{


    public function __construct(
        private CreateTransaction $createTransaction
    ) {}



    public function generate(): int
    {

        $count = 0;


        $recurringTransactions =
            RecurringTransaction::where('status', 'active')
            ->whereDate('next_run', '<=', today())
            ->get();



        foreach ($recurringTransactions as $recurring) {


            $this->createTransaction->handle([

                'user_id' => $recurring->user_id,

                'account_id' => $recurring->account_id,

                'category_id' => $recurring->category_id,

                'title' => $recurring->title,

                'description' => $recurring->description,

                'amount' => $recurring->amount,

                'type' => $recurring->type,

                'date' => $recurring->next_run,

                'parent_transaction_id' => $recurring->id,

            ]);


            $recurring->update([

                'next_run' => $this->nextDate($recurring)

            ]);


            $count++;
        }


        return $count;
    }



    private function nextDate($recurring)
    {
        return match ($recurring->frequency) {

            'daily' => $recurring->next_run->addDay(),

            'weekly' => $recurring->next_run->addWeek(),

            'monthly' => $recurring->next_run->addMonth(),

            'yearly' => $recurring->next_run->addYear(),
        };
    }
}
