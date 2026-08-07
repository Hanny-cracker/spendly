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
            'income' => $this->income,
            'expenses' => $this->expenses,
            'net_cash_flow' => $this->netCashFlow,
        ];
    }
}