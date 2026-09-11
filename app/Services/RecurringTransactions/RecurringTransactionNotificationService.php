<?php

namespace App\Services\RecurringTransactions;

use App\Data\Budget\BudgetAvailabilityData;
use App\Enums\RecurringNotificationPreference;
use App\Enums\RecurringTransactionNotificationType;
use App\Models\RecurringTransaction;
use App\Models\User;
use App\Notifications\RecurringTransactionNotification;
use Carbon\CarbonInterface;

class RecurringTransactionNotificationService
{
    public function upcoming24Hours(
        RecurringTransaction $recurring
    ): void {
        $this->deliver(
            $recurring,
            RecurringNotificationPreference::Upcoming24Hours,
            new RecurringTransactionNotification(recurringTransaction: $recurring, type: RecurringTransactionNotificationType::Upcoming24Hours),
        );
    }

    public function upcoming6Hours(
        RecurringTransaction $recurring
    ): void {
        $this->deliver(
            $recurring,
            RecurringNotificationPreference::Upcoming6Hours,
            new RecurringTransactionNotification(recurringTransaction: $recurring, type: RecurringTransactionNotificationType::Upcoming6Hours),
        );
    }

    public function generated(
        RecurringTransaction $recurring,
        CarbonInterface $scheduledFor,
    ): void {
        $this->deliver(
            $recurring,
            RecurringNotificationPreference::Success,
            new RecurringTransactionNotification(recurringTransaction: $recurring, type: RecurringTransactionNotificationType::Generated, scheduledFor: $scheduledFor),
        );
    }

    public function budgetFailure(RecurringTransaction $recurring, CarbonInterface $scheduledFor, BudgetAvailabilityData $availability): void
    {
        $this->deliver(
            $recurring,
            RecurringNotificationPreference::Failure,
            new RecurringTransactionNotification(recurringTransaction: $recurring, type: RecurringTransactionNotificationType::BudgetFailure, scheduledFor: $scheduledFor, budgetAvailability: $availability),
        );
    }

    private function deliver(RecurringTransaction $recurring, RecurringNotificationPreference $preference, RecurringTransactionNotification $notification): void
    {
        $recurring->loadMissing('user');
        $user = $recurring->user;

        if (! $user instanceof User || ! $user->wantsRecurringNotification($preference)) {
            return;
        }

        $user->notify($notification);
    }
}
