<?php

namespace App\Data\Analysis;

readonly class CategorySpendingData
{
    public function __construct(
        public int $categoryId,
        public string $categoryName,
        public float $amount,
        public int $transactionCount,
        public float $percentage,
    ) {}

    public function toArray(): array
    {
        return [
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
            'amount' => $this->amount,
            'transaction_count' => $this->transactionCount,
            'percentage' => $this->percentage,
        ];
    }
}
