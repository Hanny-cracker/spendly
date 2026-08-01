<?php

namespace App\Data\Transaction;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Carbon\Carbon;


final readonly class UpdateTransactionData
{

    public function __construct(
        public ?int $accountId,
        public ?int $categoryId,
        public string $title,
        public ?string $description,
        public ?int $transferId,
        public ?int $recurringTransactionId,
        public float $amount,
        public TransactionType $type,
        public Carbon $date,
        public TransactionStatus $status,
        public ?string $receiptPath = null,
        public ?string $notes = null,
    ){}

}