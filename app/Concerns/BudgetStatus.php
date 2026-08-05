<?php

namespace App\Enums;

enum BudgetStatus: string
{
    case Safe = 'safe';
    case Warning = 'warning';
    case Exceeded = 'exceeded';
}
