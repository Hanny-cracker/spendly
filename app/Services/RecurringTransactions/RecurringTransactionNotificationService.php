<?php

namespace App\Services\RecurringTransactions;

use App\Enums\RecurringTransactionNotificationType;
use App\Models\RecurringTransaction;
use App\Notifications\RecurringTransactionNotification;

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
        RecurringTransaction $recurring
    ): void {
        $recurring->loadMissing('user');

        $recurring->user->notify(
            new RecurringTransactionNotification(
                recurringTransaction: $recurring,
                type: RecurringTransactionNotificationType::Generated,
            )
        );
    }
}