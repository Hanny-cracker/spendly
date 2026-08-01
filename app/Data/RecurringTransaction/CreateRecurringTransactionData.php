<?php

declare(strict_types=1);

namespace App\Data\RecurringTransaction;

use App\Enums\TransactionType;
use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;

final readonly class CreateRecurringTransactionData
{
    public function __construct(

        public string $publicId,
        public int $userId,
        public int $accountId,
        public ?int $categoryId,
        public string $title,
        public ?string $description,
        public float $amount,
        public TransactionType $type,
        public RecurringFrequency $frequency,
        public int $interval,
        public \DateTimeInterface $startDate,
        public \DateTimeInterface $nextRun,
        public ?\DateTimeInterface $endDate,
        public RecurringStatus $status = RecurringStatus::Active,

    ) {
    }
}