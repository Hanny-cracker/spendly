<?php

namespace App\Data\Transfer;

use Carbon\Carbon;

final readonly class CreateTransferData
{
    public function __construct(

        public int $userId,
        public int $fromAccountId,
        public int $toAccountId,
        public float $amount,
        public ?string $description,
        public Carbon $date,

    ) {}

}
