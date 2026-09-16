<?php

namespace App\Data\Entitlements;

use App\Enums\Feature;

readonly class EntitlementResult
{
    public function __construct(
        public bool $allowed,
        public Feature $feature,
        public ?int $limit,
        public int $usage,
        public ?int $remaining,
        public ?string $reason = null,
    ) {}

    /** @return array{allowed: bool, feature: string, limit: int|null, usage: int, remaining: int|null, reason: string|null} */
    public function toArray(): array
    {
        return ['allowed' => $this->allowed, 'feature' => $this->feature->value, 'limit' => $this->limit, 'usage' => $this->usage, 'remaining' => $this->remaining, 'reason' => $this->reason];
    }
}
