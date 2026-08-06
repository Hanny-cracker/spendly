<?php

declare(strict_types=1);

namespace App\Data\RecurringTransaction;

use App\Enums\TransactionType;
use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
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

    ){}



    public function toArray():array
    {
        return [

            'title'=>$this->title,
            'description'=>$this->description,
            'amount'=>$this->amount,
            'type'=>$this->type,
            'frequency'=>$this->frequency,
            'interval'=>$this->interval,
            'end_date'=>$this->endDate,
            'status'=>$this->status,

        ];
    }
}