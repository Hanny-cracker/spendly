<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RecurringTransaction;
use App\Actions\Transactions\CreateTransaction;
use App\Data\Transaction\CreateTransactionData;
use App\Enums\RecurringStatus;
use App\Enums\TransactionStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringTransactionService
{


    public function generate(
        RecurringTransaction $recurringTransaction
    ): void {


        DB::transaction(function () use ($recurringTransaction) {


            // Create the real transaction

            app(CreateTransaction::class)->handle(

                new CreateTransactionData(

                    userId: $recurringTransaction->user_id,
                    accountId: $recurringTransaction->account_id,
                    categoryId: $recurringTransaction->category_id,
                    recurringTransactionId: $recurringTransaction->id,
                    title: $recurringTransaction->title,
                    description: $recurringTransaction->description,
                    amount: (float) $recurringTransaction->amount,
                    type: $recurringTransaction->type,
                    date: Carbon::now(),
                    status: TransactionStatus::Completed,
                    
                )

            );


            // Update recurring transaction

            $this->updateNextRun(
                $recurringTransaction
            );


        });

    }



    private function updateNextRun(
        RecurringTransaction $recurringTransaction
    ): void {


        $nextRun = $this->calculateNextRun(
            $recurringTransaction
        );


        $recurringTransaction->update([

            'next_run'=>$nextRun,
            'last_generated_at'=>now(),

        ]);



        if(
            $recurringTransaction->end_date
            &&
            $nextRun->greaterThan(
                $recurringTransaction->end_date
            )
        ){

            $recurringTransaction->update([

                'status'=>RecurringStatus::Completed

            ]);

        }

    }



    private function calculateNextRun(
        RecurringTransaction $recurringTransaction
    ): Carbon {


        $date = Carbon::parse(
            $recurringTransaction->next_run
        );


        return match($recurringTransaction->frequency->value){

            'daily'
                => $date->addDays(
                    $recurringTransaction->interval
                ),


            'weekly'
                => $date->addWeeks(
                    $recurringTransaction->interval
                ),


            'monthly'
                => $date->addMonths(
                    $recurringTransaction->interval
                ),


            'yearly'
                => $date->addYears(
                    $recurringTransaction->interval
                ),

        };


    }


}