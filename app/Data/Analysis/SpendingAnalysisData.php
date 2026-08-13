<?php

namespace App\Data\Analysis;

readonly class SpendingAnalysisData
{
    public function __construct(
        public float $totalSpent,
        public int $transactionCount,
        public float $averageTransaction,
        public ?string $topCategory,
        public float $topCategoryAmount,
        public float $topCategoryPercentage,
        public ?string $lowestCategory,
        public float $previousPeriodSpent,
        public float $changePercentage,
        public string $trend,
    ) {}

    public function toArray(): array
    {
        return [
            'total_spent' => $this->totalSpent,
            'transaction_count' => $this->transactionCount,
            'average_transaction' => $this->averageTransaction,
            'top_category' => $this->topCategory,
            'top_category_amount' => $this->topCategoryAmount,
            'top_category_percentage' => $this->topCategoryPercentage,
            'lowest_category' => $this->lowestCategory,
            'previous_period_spent' => $this->previousPeriodSpent,
            'change_percentage' => $this->changePercentage,
            'trend' => $this->trend,
        ];
    }
}