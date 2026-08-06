<?php

namespace App\Data\Budget;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use App\Enums\BudgetPeriod;

readonly class UpdateBudgetData
{
    public function __construct(
        public string $name,
        public float $amount,

        public int $alertPercentage,
        public bool $isActive,
        public ?BudgetPeriod $period = null,
        public ?CarbonInterface $startDate = null,
        public ?CarbonInterface $endDate = null,
    ) {}

    public function toArray(): array
    {
        return [
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
