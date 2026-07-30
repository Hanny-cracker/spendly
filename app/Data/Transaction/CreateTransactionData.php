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
        public ?int $parentTransactionId,
        public string $title,
        public ?string $description,
        public float $amount,
        public TransactionType $type,
        public Carbon $date,
        public TransactionStatus $status,
        public ?string $receiptPath = null,
        public ?string $notes = null,
    ){}

}