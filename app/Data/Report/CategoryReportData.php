<?php

namespace App\Data\Report;

readonly class CategoryReportData
{
    public function __construct(
        public int $categoryId,
        public string $categoryName,
        public float $total,
        public int $transactionCount,
    ) {}

    public function toArray(): array
    {
        return [
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
            'total' => (float) $this->total,
            'transaction_count' => $this->transactionCount,
        ];
    }
}
