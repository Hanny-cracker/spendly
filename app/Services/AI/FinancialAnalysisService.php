<?php

namespace App\Services\AI;

use App\Data\Analysis\FinancialAnalysisData;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;

class FinancialAnalysisService
{
    public function analyze(User $user, DateRangeData $period): FinancialAnalysisData
    {
        $period->validate();
        $base = Transaction::query()->where('user_id', $user->id)->where('status', TransactionStatus::Completed);
        $current = (clone $base)->whereBetween('date', [$period->startDate, $period->endDate]);
        $income = (float) (clone $current)->where('type', TransactionType::Income)->sum('amount');
        $expenses = (float) (clone $current)->where('type', TransactionType::Expense)->sum('amount');
        $days = max(1, $period->startDate->diffInDays($period->endDate) + 1);
        $previousStart = $period->startDate->copy()->subDays($days);
        $previousEnd = $period->startDate->copy()->subDay();
        $previousIncome = (float) (clone $base)->whereBetween('date', [$previousStart, $previousEnd])->where('type', TransactionType::Income)->sum('amount');
        $previousExpenses = (float) (clone $base)->whereBetween('date', [$previousStart, $previousEnd])->where('type', TransactionType::Expense)->sum('amount');
        $categoryRows = (clone $current)->where('type', TransactionType::Expense)->with('category')->get()->groupBy('category_id');
        $topCategories = $categoryRows->map(fn ($rows): array => ['name' => $rows->first()->category?->name ?? 'Uncategorized', 'amount' => (float) $rows->sum('amount'), 'percentage' => $expenses > 0 ? round($rows->sum('amount') / $expenses * 100, 2) : 0])->sortByDesc('amount')->values()->take(5)->all();
        $highest = (clone $current)->where('type', TransactionType::Expense)->with('category')->orderByDesc('amount')->first();

        return new FinancialAnalysisData($period->startDate, $period->endDate, $income, $expenses, $income - $expenses, $income - $expenses, $income > 0 ? ($income - $expenses) / $income * 100 : 0, $this->change($income, $previousIncome), $this->change($expenses, $previousExpenses), $topCategories, $expenses / $days, $highest ? ['title' => $highest->title, 'amount' => (float) $highest->amount, 'category' => $highest->category?->name] : null, (int) (clone $current)->count(), Account::query()->where('user_id', $user->id)->pluck('current_balance', 'name')->map(fn ($value): float => (float) $value)->all());
    }

    private function change(float $current, float $previous): float
    {
        return $previous == 0.0 ? ($current == 0.0 ? 0.0 : 100.0) : (($current - $previous) / $previous) * 100;
    }
}
