<?php

namespace App\Data\Report;

readonly class CashFlowReportData
{
    public function __construct(
        public float $income,
        public float $expenses,
        public float $netCashFlow,
    ) {}

    public function toArray(): array
    {
        return [
            'income' => (float) $this->income,
            'expenses' => (float) $this->expenses,
            'net_cash_flow' => (float) $this->netCashFlow,
        ];
    }
}