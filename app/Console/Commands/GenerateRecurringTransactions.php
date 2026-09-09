<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RecurringStatus;
use App\Exceptions\BudgetExceededException;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactions\RecurringTransactionService;
use Illuminate\Console\Command;

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
                ->where('next_run', '<=', now())
                ->get();

        foreach ($recurringTransactions as $recurring) {
            try {
                $service->generate($recurring);
                $this->info("Generated: {$recurring->title}");
            } catch (BudgetExceededException $exception) {
                $this->warn("Skipped {$recurring->title}: {$exception->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
