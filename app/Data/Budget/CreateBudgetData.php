<?php

namespace App\Data\Budget;

use App\Enums\BudgetPeriod;
use Carbon\CarbonInterface;

readonly class CreateBudgetData
{
    public function __construct(
        public int $userId,
        public int $categoryId,
        public string $name,
        public float $amount,
        public BudgetPeriod $period,
    public CarbonInterface $startDate,
    public CarbonInterface $endDate,
        public int $alertPercentage = 80,
        public bool $isActive = true,
    ) {}

    /**
     * Convert DTO to array for mass assignment.
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'category_id' => $this->categoryId,
            'name' => $this->name,
            'amount' => (float) $this->amount,
            'period' => $this->period,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'alert_percentage' => $this->alertPercentage,
            'is_active' => $this->isActive,
        ];
    }
}
