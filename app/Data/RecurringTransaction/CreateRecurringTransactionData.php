<?php

namespace App\Data\RecurringTransaction;

use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
use App\Enums\TransactionType;
use Carbon\CarbonInterface;

readonly class CreateRecurringTransactionData
{
    public function __construct(

        public int $userId,
        public int $accountId,
        public ?int $categoryId,

        public string $title,
        public ?string $description,

        public float $amount,
        public TransactionType $type,

        public RecurringFrequency $frequency,
        public int $interval,

        public CarbonInterface $startDate,
        public CarbonInterface $nextRun,
        public ?CarbonInterface $endDate,

        public RecurringStatus $status,
        public string $scheduledTime = '00:00',
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'account_id' => $this->accountId,
            'category_id' => $this->categoryId,

            'title' => $this->title,
            'description' => $this->description,

            'amount' => $this->amount,
            'type' => $this->type,

            'frequency' => $this->frequency,
            'interval'=>$this->interval,
            'start_date' => $this->startDate,
            'next_run' => $this->nextRun,
            'end_date' => $this->endDate,

            'status' => $this->status,
            'scheduled_time' => $this->scheduledTime,
        ];
    }
}
