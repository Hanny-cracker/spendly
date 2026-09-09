<?php

namespace App\Actions\Analysis;

use App\Data\Analysis\CategoryAnalysisData;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;

class CategoryAnalysis
{
    /**
     * Analyze expense categories.
     *
     * @return array<int, CategoryAnalysisData>
     */
    public function handle(
        DateRangeData $data
    ): array {

        $data->validate();

        $transactions = Transaction::query()
            ->where('user_id', $data->userId)
            ->where('status', TransactionStatus::Completed)
            ->where('type', TransactionType::Expense)
            ->whereNull('transfer_id')
            ->whereBetween('date', [
                $data->startDate,
                $data->endDate,
            ])
            ->with('category')
            ->get();

        $totalSpending = (float) $transactions->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | Group transactions by category
        |--------------------------------------------------------------------------
        */

        $categories = $transactions
            ->groupBy('category_id');

        $results = [];

        foreach ($categories as $categoryId => $categoryTransactions) {

            $category = $categoryTransactions->first()->category;

            /*
            |--------------------------------------------------------------------------
            | Ignore transactions without a category
            |--------------------------------------------------------------------------
            */

            if (! $category) {
                continue;
            }

            $total = (float) $categoryTransactions->sum('amount');

            $transactionCount = $categoryTransactions->count();

            $percentage = $totalSpending > 0
                ? ($total / $totalSpending) * 100
                : 0;

            $averageTransaction = $transactionCount > 0
                ? $total / $transactionCount
                : 0;

            $results[] = new CategoryAnalysisData(
                categoryId: $category->id,
                categoryName: $category->name,
                total: $total,
                transactionCount: $transactionCount,
                percentage: $percentage,
                averageTransaction: $averageTransaction,
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Highest spending first
        |--------------------------------------------------------------------------
        */

        usort(
            $results,
            fn (
                CategoryAnalysisData $a,
                CategoryAnalysisData $b
            ) => $b->total <=> $a->total
        );

        return $results;
    }
}
