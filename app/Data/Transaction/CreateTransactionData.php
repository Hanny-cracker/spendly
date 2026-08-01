<?php

namespace App\Data\Transaction;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Carbon\Carbon;

final readonly class CreateTransactionData
{

    public function __construct(
        public int $userId,
        public int $accountId,
        public ?int $categoryId,

        public string $title,
        public ?string $description,
        public float $amount,

        public TransactionType $type,
        public TransactionStatus $status,

        public Carbon $date,

        public ?int $transferId = null,
        public ?int $recurringTransactionId = null,

        public ?string $receiptPath = null,
        public ?string $notes = null,
    ) {}
}
