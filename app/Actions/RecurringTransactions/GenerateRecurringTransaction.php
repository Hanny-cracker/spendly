<?php

namespace App\Actions\RecurringTransactions;


use App\Models\RecurringTransaction;
use App\Models\Transaction;


class GenerateRecurringTransaction
{

public function handle(
    RecurringTransaction $recurring
): Transaction {


    $transaction = Transaction::create([

        'user_id'=>$recurring->user_id,
        'account_id'=>$recurring->account_id,
        'category_id'=>$recurring->category_id,
        'title'=>$recurring->title,
        'description'=>$recurring->description,
        'amount'=>$recurring->amount,
        'type'=>$recurring->type,
        'date'=>today(),
        'recurring_transaction_id'=>$recurring->id,

    ]);



    $recurring->update([

        'last_generated_at'=>now(),
        'next_run'=>
            $recurring->frequency
            ->nextDate(
                $recurring->next_run
            ),

    ]);

    return $transaction;


}

}