<?php

namespace App\Data\Dashboard;

use App\Data\Analysis\BudgetProgressData;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

readonly class DashboardData
{
    /**
     * @param  array<string, mixed>  $reports
     * @param  array<string, mixed>  $insights
     * @param  array<int, AccountSummaryData>  $accounts
     * @param  array<int, BudgetProgressData>  $budgets
     * @param  array<int, TransactionSummaryData>  $recentTransactions
     */
    public function __construct(
        public CarbonInterface $startDate,
        public CarbonInterface $endDate,
        public float $totalBalance,
        public array $accounts,
        public array $reports,
        public array $insights,
        public array $budgets,
        public array $recentTransactions,
        public float $savingsBalance = 0,
        public array $goalSavings = [],
    ) {}

    public function toArray(): array
    {
        return [
            'start_date' => $this->startDate->toDateString(),
            'end_date' => $this->endDate->toDateString(),

            'total_balance' => $this->totalBalance,
            'savings_balance' => $this->savingsBalance,
            'goal_savings' => $this->convertToArray($this->goalSavings),

            'accounts' => array_map(
                fn (AccountSummaryData $account) => $account->toArray(),
                $this->accounts
            ),

            'reports' => $this->convertToArray(
                $this->reports
            ),

            'insights' => $this->convertToArray(
                $this->insights
            ),

            'budgets' => array_map(
                fn (BudgetProgressData $budget) => $budget->toArray(),
                $this->budgets
            ),

            'recent_transactions' => array_map(
                fn (TransactionSummaryData $transaction) => $transaction->toArray(),
                $this->recentTransactions
            ),
        ];
    }

    private function convertToArray(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(
                fn ($item) => $this->convertToArray($item),
                $value
            );
        }

        if ($value instanceof Collection) {
            return $value
                ->map(fn ($item) => $this->convertToArray($item))
                ->all();
        }

        if ($value instanceof Model) {
            return $value->toArray();
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $this->convertToArray(
                $value->toArray()
            );
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        return $value;
    }
}
