<?php

namespace App\Enums;

enum BudgetPeriod: string
{
    case Daily = 'daily';

    case Weekly = 'weekly';

    case Monthly = 'monthly';

    case Quarterly = 'quarterly';

    case Yearly = 'yearly';

    case Custom = 'custom';
}
