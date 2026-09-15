<?php

namespace App\Data\Analysis;

use Carbon\CarbonInterface;

readonly class FinancialAnalysisData
{
    /** @param array<int, array<string, mixed>> $topCategories */
    public function __construct(
        public CarbonInterface $startDate,
        public CarbonInterface $endDate,
        public float $totalIncome,
        public float $totalExpenses,
        public float $netCashFlow,
        public float $savingsAmount,
        public float $savingsRate,
        public float $incomeChange,
        public float $expenseChange,
        public array $topCategories,
        public float $averageDailySpending,
        public ?array $highestExpense,
        public int $transactionCount,
        public array $accountBalances,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'start_date' => $this->startDate->toDateString(),
            'end_date' => $this->endDate->toDateString(),
            'total_income' => $this->totalIncome,
            'total_expenses' => $this->totalExpenses,
            'net_cash_flow' => $this->netCashFlow,
            'savings_amount' => $this->savingsAmount,
            'savings_rate' => $this->savingsRate,
            'income_change' => $this->incomeChange,
            'expense_change' => $this->expenseChange,
            'top_categories' => $this->topCategories,
            'average_daily_spending' => $this->averageDailySpending,
            'highest_expense' => $this->highestExpense,
            'transaction_count' => $this->transactionCount,
            'account_balances' => $this->accountBalances,
        ];
    }
}
