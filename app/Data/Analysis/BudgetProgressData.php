<?php

namespace App\Data\Analysis;

readonly class BudgetProgressData
{
    public function __construct(
        public int $budgetId,
        public string $budgetName,
        public float $budgetAmount,
        public float $spentAmount,
        public float $remainingAmount,
        public float $percentageUsed,
        public string $status,
    ) {}

    public function toArray(): array
    {
        return [
            'budget_id' => $this->budgetId,
            'budget_name' => $this->budgetName,
            'budget_amount' => $this->budgetAmount,
            'spent_amount' => $this->spentAmount,
            'remaining_amount' => $this->remainingAmount,
            'percentage_used' => $this->percentageUsed,
            'status' => $this->status,
        ];
    }
}