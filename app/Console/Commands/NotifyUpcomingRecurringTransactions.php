<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RecurringStatus;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactions\RecurringTransactionNotificationService;
use Illuminate\Console\Command;

class NotifyUpcomingRecurringTransactions extends Command
{
    protected $signature = 'transactions:notify-upcoming';

    protected $description =
        'Notify users about upcoming recurring transactions';

    public function handle(
        RecurringTransactionNotificationService $notificationService
    ): int {

        $now = now();

        $recurringTransactions = RecurringTransaction::query()
            ->where('status', RecurringStatus::Active)
            ->whereNotNull('next_run')
            ->get();

        foreach ($recurringTransactions as $recurring) {

            $nextRun = $recurring->next_run;

            $hoursUntilPayment = $now->diffInHours(
                $nextRun,
                false
            );

            /*
            |--------------------------------------------------------------------------
            | 24-hour notification
            |--------------------------------------------------------------------------
            */

            if (
                $hoursUntilPayment <= 24
                && $hoursUntilPayment > 12
                && $recurring->last_24h_notified_at === null
            ) {
                $notificationService->upcoming24Hours(
                    $recurring
                );

                $recurring->update([
                    'last_24h_notified_at' => $now,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 6-hour notification
            |--------------------------------------------------------------------------
            */

            if (
                $hoursUntilPayment <= 6
                && $hoursUntilPayment > 0
                && $recurring->last_6h_notified_at === null
            ) {
                $notificationService->upcoming6Hours(
                    $recurring
                );

                $recurring->update([
                    'last_6h_notified_at' => $now,
                ]);
            }
        }

        return self::SUCCESS;
    }
}