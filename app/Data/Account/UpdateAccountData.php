<?php

namespace App\Data\Account;

use App\Enums\AccountType;

final readonly class UpdateAccountData
{
    public function __construct(
        public string $name,
        public AccountType $type,
        public string $currency,
        public ?string $color,
        public bool $isDefault,
    ) {}
}
