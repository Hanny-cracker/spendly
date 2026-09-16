<?php

namespace App\Filament\Widgets;

use App\Enums\RecurringStatus;
use App\Models\RecurringTransaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RecurringHealthOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Active schedules', RecurringTransaction::query()->where('status', RecurringStatus::Active)->count()),
            Stat::make('Due schedules', RecurringTransaction::query()->where('status', RecurringStatus::Active)->where('next_run', '<=', now('UTC'))->count()),
            Stat::make('Overdue schedules', RecurringTransaction::query()->where('status', RecurringStatus::Active)->where('next_run', '<', now('UTC')->subHour())->count()),
        ];
    }
}
