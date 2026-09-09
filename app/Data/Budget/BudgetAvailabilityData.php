<?php

namespace App\Data\Budget;

final readonly class BudgetAvailabilityData
{
    public function __construct(
        public int $budgetId,
        public string $budgetName,
        public string $categoryName,
        public float $limit,
        public float $spent,
        public float $remaining,
        public float $requested = 0,
        public float $shortfall = 0,
    ) {}

    /** @return array<string, int|string|float> */
    public function toArray(): array
    {
        return [
            'budget_id' => $this->budgetId,
            'budget_name' => $this->budgetName,
            'category_name' => $this->categoryName,
            'limit' => $this->limit,
            'spent' => $this->spent,
            'remaining' => $this->remaining,
            'requested' => $this->requested,
            'shortfall' => $this->shortfall,
        ];
    }
}
