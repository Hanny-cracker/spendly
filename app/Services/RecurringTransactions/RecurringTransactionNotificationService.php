<?php

namespace App\Services\RecurringTransactions;

use App\Enums\RecurringTransactionNotificationType;
use App\Data\Budget\BudgetAvailabilityData;
use App\Models\RecurringTransaction;
use App\Notifications\RecurringTransactionNotification;
use Carbon\CarbonInterface;

class RecurringTransactionNotificationService
{
    public function upcoming24Hours(
        RecurringTransaction $recurring
    ): void {
        $recurring->loadMissing('user');

        $recurring->user->notify(
            new RecurringTransactionNotification(
                recurringTransaction: $recurring,
                type: RecurringTransactionNotificationType::Upcoming24Hours,
            )
        );
    }

    public function upcoming6Hours(
        RecurringTransaction $recurring
    ): void {
        $recurring->loadMissing('user');

        $recurring->user->notify(
            new RecurringTransactionNotification(
                recurringTransaction: $recurring,
                type: RecurringTransactionNotificationType::Upcoming6Hours,
            )
        );
    }

    public function generated(
        RecurringTransaction $recurring,
        CarbonInterface $scheduledFor,
    ): void {
        $recurring->loadMissing('user');

        $recurring->user->notify(
            new RecurringTransactionNotification(
                recurringTransaction: $recurring,
                type: RecurringTransactionNotificationType::Generated,
                scheduledFor: $scheduledFor,
            )
        );
    }

    public function budgetFailure(RecurringTransaction $recurring, CarbonInterface $scheduledFor, BudgetAvailabilityData $availability): void
    {
        $recurring->loadMissing('user');
        $recurring->user->notify(new RecurringTransactionNotification(
            recurringTransaction: $recurring,
            type: RecurringTransactionNotificationType::BudgetFailure,
            scheduledFor: $scheduledFor,
            budgetAvailability: $availability,
        ));
    }
}
