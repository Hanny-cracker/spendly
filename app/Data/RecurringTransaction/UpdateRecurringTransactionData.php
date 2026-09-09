<?php

declare(strict_types=1);

namespace App\Data\RecurringTransaction;

use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
use App\Enums\TransactionType;
use Carbon\CarbonInterface;

readonly class UpdateRecurringTransactionData
{
    public function __construct(

        public string $title,
        public ?string $description,
        public float $amount,
        public TransactionType $type,
        public RecurringFrequency $frequency,
        public int $interval,
        public ?CarbonInterface $endDate,
        public RecurringStatus $status,
        public ?int $userId = null,
        public ?int $accountId = null,
        public ?int $categoryId = null,
        public ?CarbonInterface $startDate = null,
        public ?string $scheduledTime = null,

    ) {}

    public function toArray(): array
    {
        $values = [

            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'type' => $this->type,
            'frequency' => $this->frequency,
            'interval' => $this->interval,
            'end_date' => $this->endDate,
            'status' => $this->status,
        ];

        if ($this->accountId !== null) {
            $values['account_id'] = $this->accountId;
        }
        if ($this->categoryId !== null) {
            $values['category_id'] = $this->categoryId;
        }
        if ($this->startDate !== null) {
            $values['start_date'] = $this->startDate;
        }
        if ($this->scheduledTime !== null) {
            $values['scheduled_time'] = $this->scheduledTime;
        }

        return $values;
    }
}
