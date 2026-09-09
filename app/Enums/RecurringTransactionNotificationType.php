<?php

namespace App\Enums;

enum RecurringTransactionNotificationType: string
{
    case Upcoming24Hours = 'upcoming_24_hours';

    case Upcoming6Hours = 'upcoming_6_hours';

    case Generated = 'generated';

    case BudgetFailure = 'budget_failure';
}
