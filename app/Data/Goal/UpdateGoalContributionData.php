<?php

namespace App\Data\Goal;

use Carbon\CarbonInterface;

final readonly class UpdateGoalContributionData
{
    public function __construct(
        public int $userId,
        public float $amount,
        public CarbonInterface $contributedAt,
        public ?int $accountId = null,
        public ?string $note = null,
    ) {}
}
