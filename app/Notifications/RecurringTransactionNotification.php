<?php

namespace App\Notifications;

use App\Enums\RecurringTransactionNotificationType;
use App\Models\RecurringTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RecurringTransactionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public RecurringTransaction $recurringTransaction,
        public RecurringTransactionNotificationType $type,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->type->value,

            'recurring_transaction_id' =>
                $this->recurringTransaction->id,

            'title' =>
                $this->recurringTransaction->title,

            'amount' =>
                (float) $this->recurringTransaction->amount,

            'next_run' =>
                $this->recurringTransaction->next_run
                    ?->toDateTimeString(),

            'message' => $this->message(),
        ];
    }

    protected function message(): string
    {
        $title = $this->recurringTransaction->title;

        $amount = number_format(
            (float) $this->recurringTransaction->amount,
            2
        );

        return match ($this->type) {

            RecurringTransactionNotificationType::Upcoming24Hours =>
                "Your recurring transaction '{$title}' "
                . "of {$amount} is scheduled within 24 hours.",

            RecurringTransactionNotificationType::Upcoming6Hours =>
                "Your recurring transaction '{$title}' "
                . "of {$amount} is scheduled within 6 hours.",

            RecurringTransactionNotificationType::Generated =>
                "Your recurring transaction '{$title}' "
                . "of {$amount} has been generated successfully.",
        };
    }
}