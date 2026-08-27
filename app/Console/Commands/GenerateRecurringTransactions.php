<?php

declare(strict_types=1);

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactions\RecurringTransactionService;
use App\Enums\RecurringStatus;


class GenerateRecurringTransactions extends Command
{

    protected $signature = 'transactions:generate-recurring';


    protected $description =
    'Generate due recurring transactions';



    public function handle(
        RecurringTransactionService $service
    ): int {


        $recurringTransactions =
            RecurringTransaction::query()

            ->where(
                'status',
                RecurringStatus::Active
            )

            ->whereDate('next_run', '<=', now())
            ->get();



        foreach ($recurringTransactions as $recurring) {


            $service->generate(
                $recurring
            );


            $this->info(
                "Generated: {$recurring->title}"
            );
        }



        return self::SUCCESS;
    }
}
