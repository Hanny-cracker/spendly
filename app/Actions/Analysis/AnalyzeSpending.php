<?php

namespace App\Actions\Analysis;

use App\Data\Analysis\CategorySpendingData;
use App\Data\Analysis\SpendingAnalysisData;
use App\Data\Report\DateRangeData;
use App\Queries\Analysis\SpendingQueries;

class AnalyzeSpending
{
    public function __construct(
        protected SpendingQueries $queries,
    ) {}

    public function handle(
        DateRangeData $data
    ): SpendingAnalysisData {

        $transactions = $this->queries->expenses($data);

        $totalSpent = (float) $transactions->sum(
            fn ($transaction) => (float) $transaction->amount
        );

        $transactionCount = $transactions->count();

        $averageTransaction = $transactionCount > 0
            ? $totalSpent / $transactionCount
            : 0;

        /*
         * Group transactions by category.
         */
        $categories = $transactions
            ->groupBy('category_id')
            ->map(function ($transactions) use ($totalSpent) {

                $amount = (float) $transactions->sum(
                    fn ($transaction) => (float) $transaction->amount
                );

                $category = $transactions->first()->category;

                return new CategorySpendingData(
                    categoryId: $category?->id ?? 0,
                    categoryName: $category?->name ?? 'Uncategorized',
                    amount: $amount,
                    transactionCount: $transactions->count(),
                    percentage: $totalSpent > 0
                        ? ($amount / $totalSpent) * 100
                        : 0,
                );
            })
            ->sortByDesc('amount')
            ->values();

        $topCategory = $categories->first();

        $lowestCategory = $categories->last();

        /*
         * Calculate previous period.
         */
        $previousPeriod = $this->previousPeriod($data);

        $previousTransactions = $this->queries->expenses(
            $previousPeriod
        );

        $previousPeriodSpent = (float) $previousTransactions->sum(
            fn ($transaction) => (float) $transaction->amount
        );

        /*
         * Calculate percentage change.
         */
        $changePercentage = $this->calculatePercentageChange(
            $previousPeriodSpent,
            $totalSpent
        );

        $trend = $this->determineTrend($changePercentage);

        return new SpendingAnalysisData(
            totalSpent: $totalSpent,
            transactionCount: $transactionCount,
            averageTransaction: $averageTransaction,
            topCategory: $topCategory?->categoryName,
            topCategoryAmount: $topCategory?->amount ?? 0,
            topCategoryPercentage: $topCategory?->percentage ?? 0,
            lowestCategory: $lowestCategory?->categoryName,
            previousPeriodSpent: $previousPeriodSpent,
            changePercentage: $changePercentage,
            trend: $trend,
        );
    }

    /**
     * Generate detailed category analysis.
     *
     * @return array<int, CategorySpendingData>
     */
    public function byCategory(
        DateRangeData $data
    ): array {

        $transactions = $this->queries->expenses($data);

        $totalSpent = (float) $transactions->sum(
            fn ($transaction) => (float) $transaction->amount
        );

        return $transactions
            ->groupBy('category_id')
            ->map(function ($transactions) use ($totalSpent) {

                $amount = (float) $transactions->sum(
                    fn ($transaction) => (float) $transaction->amount
                );

                $category = $transactions->first()->category;

                return new CategorySpendingData(
                    categoryId: $category?->id ?? 0,
                    categoryName: $category?->name ?? 'Uncategorized',
                    amount: $amount,
                    transactionCount: $transactions->count(),
                    percentage: $totalSpent > 0
                        ? ($amount / $totalSpent) * 100
                        : 0,
                );
            })
            ->sortByDesc('amount')
            ->values()
            ->all();
    }

    /**
     * Calculate previous period with the same duration.
     */
    protected function previousPeriod(
        DateRangeData $data
    ): DateRangeData {

        $days = $data->startDate->diffInDays(
            $data->endDate
        ) + 1;

        $previousEnd = $data->startDate->copy()->subDay();

        $previousStart = $previousEnd
            ->copy()
            ->subDays($days - 1);

        return new DateRangeData(
            userId: $data->userId,
            startDate: $previousStart,
            endDate: $previousEnd,
        );
    }

    /**
     * Calculate percentage change.
     */
    protected function calculatePercentageChange(
        float $previous,
        float $current
    ): float {

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    /**
     * Determine spending trend.
     */
    protected function determineTrend(
        float $changePercentage
    ): string {

        if ($changePercentage > 5) {
            return 'increasing';
        }

        if ($changePercentage < -5) {
            return 'decreasing';
        }

        return 'stable';
    }
}
