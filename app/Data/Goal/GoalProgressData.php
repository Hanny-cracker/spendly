<?php

namespace App\Data\Goal;

final readonly class GoalProgressData
{
    public function __construct(
        public int $goalId, public string $publicId, public string $name, public ?string $description,
        public float $targetAmount, public float $currentAmount, public float $remainingAmount, public float $percentage,
        public string $status, public ?string $targetDate, public bool $isOverdue,
    ) {}

    public function toArray(): array
    {
        return ['goal_id' => $this->goalId, 'public_id' => $this->publicId, 'name' => $this->name, 'description' => $this->description, 'target_amount' => $this->targetAmount, 'current_amount' => $this->currentAmount, 'remaining_amount' => $this->remainingAmount, 'percentage' => $this->percentage, 'status' => $this->status, 'target_date' => $this->targetDate, 'is_overdue' => $this->isOverdue];
    }
}
