<?php

namespace App\Actions\Reports;

use App\Data\Report\CategoryReportData;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionType;
use App\Queries\Reports\TransactionQueries;

class GenerateCategoryReport
{
    public function __construct(
        protected TransactionQueries $queries,
    ) {}

    /**
     * @return array<int, CategoryReportData>
     */
    public function handle(
        DateRangeData $data,
        ?TransactionType $type = TransactionType::Expense,
    ): array {

        $transactions = $this->queries->withCategories($data, $type);

        return $transactions
            ->groupBy('category_id')
            ->map(function ($categoryTransactions) {

                $firstTransaction = $categoryTransactions->first();
                $total = (float) $categoryTransactions->sum('amount');
                $transactionCount = $categoryTransactions->count();

                return new CategoryReportData(
                    categoryId: $firstTransaction->category_id,
                    categoryName: $firstTransaction->category->name,
                    total: $total,
                    transactionCount: $transactionCount,
                );
            })
            ->values()
            ->all();
    }
}
