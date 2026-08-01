<?php

declare(strict_types=1);

namespace App\Data\RecurringTransaction;

use App\Enums\TransactionType;
use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;

final readonly class UpdateRecurringTransactionData
{
    public function __construct(

        public ?int $accountId = null,
        public ?int $categoryId = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?float $amount = null,
        public ?TransactionType $type = null,
        public ?RecurringFrequency $frequency = null,
        public ?int $interval = null,
        public ?\DateTimeInterface $startDate = null,
        public ?\DateTimeInterface $nextRun = null,
        public ?\DateTimeInterface $endDate = null,
        public ?RecurringStatus $status = null,

    ) {
    }
}