<?php

namespace App\Data\Report;

readonly class ExpenseReportData
{
    public function __construct(
        public float $total,
        public int $transactionCount,
        public float $average,
    ) {}

    public function toArray(): array
    {
        return [
            'total' => (float) $this->total,
            'transaction_count' => $this->transactionCount,
            'average' => (float) $this->average,
        ];
    }
}