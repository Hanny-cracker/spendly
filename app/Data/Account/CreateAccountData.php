<?php

namespace App\Data\Account;

use App\Enums\AccountType;

final readonly class CreateAccountData
{
    public function __construct(
        public int $userId,
        public string $name,
        public AccountType $type,
        public string $currency,
        public float $openingBalance = 0,
        public ?string $color = null,
        public bool $isDefault = false,
    ) {}

}
