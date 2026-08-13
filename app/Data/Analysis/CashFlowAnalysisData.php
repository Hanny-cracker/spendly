<?php

namespace App\Data\Analysis;

readonly class CashFlowAnalysisData
{
    public function __construct(
        public float $totalIncome,
        public float $totalExpenses,
        public float $netCashFlow,
        public int $incomeTransactionCount,
        public int $expenseTransactionCount,
        public float $savingsRate,
        public string $status,
        public string $trend,
    ) {}

    public function toArray(): array
    {
        return [
            'total_income' => $this->totalIncome,
            'total_expenses' => $this->totalExpenses,
            'net_cash_flow' => $this->netCashFlow,
            'income_transaction_count' => $this->incomeTransactionCount,
            'expense_transaction_count' => $this->expenseTransactionCount,
            'savings_rate' => $this->savingsRate,
            'status' => $this->status,
            'trend' => $this->trend,
        ];
    }
}