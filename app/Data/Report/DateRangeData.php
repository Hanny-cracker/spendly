<?php

namespace App\Data\Report;

use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

readonly class DateRangeData
{
    public function __construct(
        public int $userId,
        public CarbonInterface $startDate,
        public CarbonInterface $endDate,
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];
    }

    public function validate(): void
    {
        if ($this->endDate->lt($this->startDate)) {
            throw ValidationException::withMessages([
                'end_date' => 'End date must be after the start date.',
            ]);
        }
    }
}