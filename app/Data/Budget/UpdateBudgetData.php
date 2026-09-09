<?php

namespace App\Data\Budget;

use App\Enums\BudgetPeriod;
use Carbon\CarbonInterface;

readonly class UpdateBudgetData
{
    public function __construct(
        public string $name,
        public float $amount,
        public int $alertPercentage,
        public bool $isActive,
        public ?int $categoryId = null,
        public ?BudgetPeriod $period = null,
        public ?CarbonInterface $startDate = null,
        public ?CarbonInterface $endDate = null,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'category_id' => $this->categoryId,
            'amount' => (float) $this->amount,
            'period' => $this->period,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'alert_percentage' => $this->alertPercentage,
            'is_active' => $this->isActive,
        ];
    }
}
