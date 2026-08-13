<?php

namespace App\Actions\Analysis;

use App\Data\Analysis\IncomeAnalysisData;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;

class IncomeAnalysis
{
    public function handle(DateRangeData $data): IncomeAnalysisData
    {
        $data->validate();

        $transactions = Transaction::query()
            ->where('user_id', $data->userId)
            ->where('type', TransactionType::Income)
            ->where('status', TransactionStatus::Completed)
            ->whereBetween('date', [
                $data->startDate,
                $data->endDate,
            ])
            ->with('category')
            ->get();

        $count = $transactions->count();

        /*
        |--------------------------------------------------------------------------
        | No income
        |--------------------------------------------------------------------------
        */

        if ($count === 0) {
            return new IncomeAnalysisData(
                totalIncome: 0,
                transactionCount: 0,
                averageIncome: 0,
                largestIncome: 0,
                smallestIncome: 0,
                topCategoryId: null,
                topCategoryName: null,
                topCategoryPercentage: 0,
                trend: 'stable',
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Basic statistics
        |--------------------------------------------------------------------------
        */

        $totalIncome = (float) $transactions->sum('amount');

        $averageIncome = $totalIncome / $count;

        $largestIncome = (float) $transactions->max('amount');

        $smallestIncome = (float) $transactions->min('amount');

        /*
        |--------------------------------------------------------------------------
        | Top income category
        |--------------------------------------------------------------------------
        */

        $categoryTotals = $transactions
            ->filter(fn ($transaction) => $transaction->category !== null)
            ->groupBy('category_id')
            ->map(function ($categoryTransactions) {
                return [
                    'category' => $categoryTransactions->first()->category,
                    'total' => (float) $categoryTransactions->sum('amount'),
                ];
            })
            ->sortByDesc('total');

        $topCategory = $categoryTotals->first();

        $topCategoryId = $topCategory['category']->id ?? null;

        $topCategoryName = $topCategory['category']->name ?? null;

        $topCategoryPercentage = $totalIncome > 0
            ? (($topCategory['total'] ?? 0) / $totalIncome) * 100
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Income trend
        |--------------------------------------------------------------------------
        */

        $trend = $this->calculateTrend($data);

        return new IncomeAnalysisData(
            totalIncome: $totalIncome,
            transactionCount: $count,
            averageIncome: $averageIncome,
            largestIncome: $largestIncome,
            smallestIncome: $smallestIncome,
            topCategoryId: $topCategoryId,
            topCategoryName: $topCategoryName,
            topCategoryPercentage: $topCategoryPercentage,
            trend: $trend,
        );
    }

    private function calculateTrend(DateRangeData $data): string
    {
        $days = $data->startDate->diffInDays($data->endDate);

        if ($days <= 0) {
            return 'stable';
        }

        $midpoint = $data->startDate->copy()->addDays(
            (int) floor($days / 2)
        );

        $firstPeriodIncome = Transaction::query()
            ->where('user_id', $data->userId)
            ->where('type', TransactionType::Income)
            ->where('status', TransactionStatus::Completed)
            ->whereBetween('date', [
                $data->startDate,
                $midpoint,
            ])
            ->sum('amount');

        $secondPeriodIncome = Transaction::query()
            ->where('user_id', $data->userId)
            ->where('type', TransactionType::Income)
            ->where('status', TransactionStatus::Completed)
            ->whereBetween('date', [
                $midpoint->copy()->addDay(),
                $data->endDate,
            ])
            ->sum('amount');

        $firstPeriodIncome = (float) $firstPeriodIncome;
        $secondPeriodIncome = (float) $secondPeriodIncome;

        if ($firstPeriodIncome === 0.0 && $secondPeriodIncome === 0.0) {
            return 'stable';
        }

        if ($firstPeriodIncome === 0.0) {
            return 'increasing';
        }

        $changePercentage = (
            ($secondPeriodIncome - $firstPeriodIncome)
            / $firstPeriodIncome
        ) * 100;

        if ($changePercentage > 10) {
            return 'increasing';
        }

        if ($changePercentage < -10) {
            return 'decreasing';
        }

        return 'stable';
    }
}