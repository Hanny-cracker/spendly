<?php

namespace App\Enums;

enum RecurringNotificationPreference: string
{
    case Upcoming24Hours = 'notify_recurring_24h';
    case Upcoming6Hours = 'notify_recurring_6h';
    case Success = 'notify_recurring_success';
    case Failure = 'notify_recurring_failure';
}
