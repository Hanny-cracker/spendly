<?php

namespace App\Data\Analysis;

readonly class IncomeAnalysisData
{
    public function __construct(
        public float $totalIncome,
        public int $transactionCount,
        public float $averageIncome,
        public float $largestIncome,
        public float $smallestIncome,
        public ?int $topCategoryId,
        public ?string $topCategoryName,
        public float $topCategoryPercentage,
        public string $trend,
    ) {}

    public function toArray(): array
    {
        return [
            'total_income' => $this->totalIncome,
            'transaction_count' => $this->transactionCount,
            'average_income' => $this->averageIncome,
            'largest_income' => $this->largestIncome,
            'smallest_income' => $this->smallestIncome,
            'top_category_id' => $this->topCategoryId,
            'top_category_name' => $this->topCategoryName,
            'top_category_percentage' => $this->topCategoryPercentage,
            'trend' => $this->trend,
        ];
    }
}