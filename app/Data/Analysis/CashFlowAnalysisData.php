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
            'total_income' => (float) $this->totalIncome,
            'total_expenses' => (float) $this->totalExpenses,
            'net_cash_flow' => (float) $this->netCashFlow,
            'income_transaction_count' => (int) $this->incomeTransactionCount,
            'expense_transaction_count' => (int) $this->expenseTransactionCount,
            'savings_rate' => (float) $this->savingsRate,
            'status' => $this->status,
            'trend' => $this->trend,
        ];
    }
}
