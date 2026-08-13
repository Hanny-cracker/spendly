<?php

namespace App\Actions\Analysis;

use App\Data\Analysis\CashFlowAnalysisData;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;

class CashFlowAnalysis
{
    public function handle(DateRangeData $data): CashFlowAnalysisData
    {
        $data->validate();

        $transactions = Transaction::query()
            ->where('user_id', $data->userId)
            ->where('status', TransactionStatus::Completed)
            ->whereBetween('date', [
                $data->startDate,
                $data->endDate,
            ])
            ->get();

        $incomeTransactions = $transactions
            ->where('type', TransactionType::Income);

        $expenseTransactions = $transactions
            ->where('type', TransactionType::Expense);

        $totalIncome = (float) $incomeTransactions->sum('amount');

        $totalExpenses = (float) $expenseTransactions->sum('amount');

        $netCashFlow = $totalIncome - $totalExpenses;

        $incomeTransactionCount = $incomeTransactions->count();

        $expenseTransactionCount = $expenseTransactions->count();

        /*
        |--------------------------------------------------------------------------
        | Savings Rate
        |--------------------------------------------------------------------------
        |
        | Savings rate = (Income - Expenses) / Income × 100
        |
        */

        $savingsRate = $totalIncome > 0
            ? ($netCashFlow / $totalIncome) * 100
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Cash Flow Status
        |--------------------------------------------------------------------------
        */

        $status = $this->calculateStatus($netCashFlow);

        /*
        |--------------------------------------------------------------------------
        | Cash Flow Trend
        |--------------------------------------------------------------------------
        */

        $trend = $this->calculateTrend($data);

        return new CashFlowAnalysisData(
            totalIncome: $totalIncome,
            totalExpenses: $totalExpenses,
            netCashFlow: $netCashFlow,
            incomeTransactionCount: $incomeTransactionCount,
            expenseTransactionCount: $expenseTransactionCount,
            savingsRate: $savingsRate,
            status: $status,
            trend: $trend,
        );
    }

    private function calculateStatus(float $netCashFlow): string
    {
        if ($netCashFlow > 0) {
            return 'positive';
        }

        if ($netCashFlow < 0) {
            return 'negative';
        }

        return 'neutral';
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

        $firstPeriodCashFlow = $this->calculatePeriodCashFlow(
            $data->userId,
            $data->startDate,
            $midpoint
        );

        $secondPeriodCashFlow = $this->calculatePeriodCashFlow(
            $data->userId,
            $midpoint->copy()->addDay(),
            $data->endDate
        );

        /*
        |--------------------------------------------------------------------------
        | Handle zero periods
        |--------------------------------------------------------------------------
        */

        if (
            $firstPeriodCashFlow === 0.0
            && $secondPeriodCashFlow === 0.0
        ) {
            return 'stable';
        }

        if ($firstPeriodCashFlow === 0.0) {
            return $secondPeriodCashFlow > 0
                ? 'increasing'
                : 'decreasing';
        }

        $changePercentage = (
            ($secondPeriodCashFlow - $firstPeriodCashFlow)
            / abs($firstPeriodCashFlow)
        ) * 100;

        if ($changePercentage > 10) {
            return 'increasing';
        }

        if ($changePercentage < -10) {
            return 'decreasing';
        }

        return 'stable';
    }

    private function calculatePeriodCashFlow(
        int $userId,
        $startDate,
        $endDate
    ): float {
        $transactions = Transaction::query()
            ->where('user_id', $userId)
            ->where('status', TransactionStatus::Completed)
            ->whereBetween('date', [
                $startDate,
                $endDate,
            ])
            ->get();

        $income = (float) $transactions
            ->where('type', TransactionType::Income)
            ->sum('amount');

        $expenses = (float) $transactions
            ->where('type', TransactionType::Expense)
            ->sum('amount');

        return $income - $expenses;
    }
}