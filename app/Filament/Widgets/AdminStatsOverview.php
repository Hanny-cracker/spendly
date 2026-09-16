<?php

namespace App\Filament\Widgets;

use App\Enums\RecurringStatus;
use App\Enums\SubscriptionStatus;
use App\Models\RecurringTransaction;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::query()->count()),
            Stat::make('New Users This Month', User::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count()),
            Stat::make('Active Subscribers', Subscription::query()->where('status', SubscriptionStatus::Active)->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count()),
            Stat::make('Trial Subscriptions', Subscription::query()->where('status', SubscriptionStatus::Trial)->count()),
            Stat::make('Expired Subscriptions', Subscription::query()->where('status', SubscriptionStatus::Expired)->orWhere(fn ($query) => $query->where('status', SubscriptionStatus::Active)->where('expires_at', '<=', now()))->count()),
            Stat::make('Expiring Within 7 Days', Subscription::query()->where('status', SubscriptionStatus::Active)->whereBetween('expires_at', [now(), now()->addDays(7)])->count()),
            Stat::make('Revenue This Month (XAF)', Subscription::query()->where('currency', 'XAF')->whereBetween('activated_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount')),
            Stat::make('Revenue All Time (XAF)', Subscription::query()->where('currency', 'XAF')->sum('amount')),
            Stat::make('Total Transactions', Transaction::query()->count()),
            Stat::make('Transactions This Month', Transaction::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count()),
            Stat::make('Active Recurring Schedules', RecurringTransaction::query()->where('status', RecurringStatus::Active)->count()),
        ];
    }
}
