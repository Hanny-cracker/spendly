<?php

namespace App\Data\Dashboard;

readonly class AccountSummaryData
{
    public function __construct(
        public int $id,
        public string $publicId,
        public string $name,
        public string $type,
        public string $currency,
        public float $currentBalance,
        public ?string $color,
        public bool $isDefault,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->publicId,
            'name' => $this->name,
            'type' => $this->type,
            'currency' => $this->currency,
            'current_balance' => $this->currentBalance,
            'color' => $this->color,
            'is_default' => $this->isDefault,
        ];
    }
}
