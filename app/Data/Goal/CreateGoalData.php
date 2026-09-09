<?php

namespace App\Data\Goal;

use Carbon\CarbonInterface;

final readonly class CreateGoalData
{
    public function __construct(public int $userId, public string $name, public float $targetAmount, public ?CarbonInterface $targetDate = null, public ?string $description = null) {}
}
