<?php

namespace App\Notifications;

use App\Data\Budget\BudgetAvailabilityData;
use App\Enums\RecurringTransactionNotificationType;
use App\Models\RecurringTransaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;

#[DeleteWhenMissingModels]
class RecurringTransactionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public RecurringTransaction $recurringTransaction,
        public RecurringTransactionNotificationType $type,
        public ?CarbonInterface $scheduledFor = null,
        public ?BudgetAvailabilityData $budgetAvailability = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $timezone = $notifiable instanceof User
            ? $notifiable->timezone()
            : $this->recurringTransaction->timezone;

        return [
            'type' => $this->type->value,

            'notification_title' => $this->notificationTitle(),

            'recurring_transaction_id' => $this->recurringTransaction->id,

            'title' => $this->recurringTransaction->title,

            'amount' => (float) $this->recurringTransaction->amount,

            'next_run' => $this->recurringTransaction->next_run
                ?->copy()
                ?->setTimezone($timezone)
                ?->toDateTimeString(),

            'scheduled_for' => $this->scheduledFor?->copy()->setTimezone($timezone)->toDateTimeString(),

            'message' => $this->message($timezone),
        ];
    }

    protected function message(string $timezone): string
    {
        $title = $this->recurringTransaction->title;

        $amount = number_format(
            (float) $this->recurringTransaction->amount,
            2
        );

        return match ($this->type) {

            RecurringTransactionNotificationType::Upcoming24Hours => "Your recurring transaction '{$title}' "
                ."of {$amount} is scheduled within 24 hours.",

            RecurringTransactionNotificationType::Upcoming6Hours => "Your recurring transaction '{$title}' "
                ."of {$amount} is scheduled within 6 hours.",

            RecurringTransactionNotificationType::Generated => "Your recurring {$this->recurringTransaction->type->value} '{$title}' "
                ."of {$amount} scheduled for {$this->scheduledFor?->copy()->setTimezone($timezone)->format('d M Y \a\t H:i')} was recorded successfully.",

            RecurringTransactionNotificationType::BudgetFailure => "Your recurring expense '{$title}' of {$amount} was not recorded. "
                ."The {$this->budgetAvailability?->categoryName} budget has only "
                .number_format((float) $this->budgetAvailability?->remaining, 2).' FCFA remaining.',
        };
    }

    private function notificationTitle(): string
    {
        return match ($this->type) {
            RecurringTransactionNotificationType::Upcoming24Hours,
            RecurringTransactionNotificationType::Upcoming6Hours => 'Recurring transaction upcoming',
            RecurringTransactionNotificationType::Generated => $this->recurringTransaction->type->isIncome()
                ? 'Recurring deposit recorded'
                : 'Recurring expense recorded',
            RecurringTransactionNotificationType::BudgetFailure => 'Recurring expense not recorded',
        };
    }
}
