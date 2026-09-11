<?php

namespace App\Data\Analysis;

readonly class CategoryAnalysisData
{
    public function __construct(
        public int $categoryId,
        public string $categoryName,
        public float $total,
        public int $transactionCount,
        public float $percentage,
        public float $averageTransaction,
    ) {}

    public function toArray(): array
    {
        return [
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
            'total' => $this->total,
            'transaction_count' => $this->transactionCount,
            'percentage' => $this->percentage,
            'average_transaction' => $this->averageTransaction,
        ];
    }
}
