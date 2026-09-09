<?php

namespace App\Data\Report;

use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final readonly class FinancialReportData
{
    public function __construct(
        public CarbonInterface $startDate,
        public CarbonInterface $endDate,
        public mixed $cashFlow,
        public array $expenseCategories,
        public array $incomeCategories,
        public array $accountActivity,
        public array $transactionSummary,
        public array $insights,
    ) {}

    public function toArray(): array
    {
        return $this->normalize(['start_date' => $this->startDate, 'end_date' => $this->endDate, 'cash_flow' => $this->cashFlow, 'expense_categories' => $this->expenseCategories, 'income_categories' => $this->incomeCategories, 'account_activity' => $this->accountActivity, 'transaction_summary' => $this->transactionSummary, 'insights' => $this->insights]);
    }

    private function normalize(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }
        if ($value instanceof CarbonInterface) {
            return $value->toDateString();
        }
        if ($value instanceof Model) {
            return $this->normalize($value->toArray());
        }
        if ($value instanceof Collection) {
            return $this->normalize($value->all());
        }
        if (is_object($value) && method_exists($value, 'toArray')) {
            return $this->normalize($value->toArray());
        }
        if (is_array($value)) {
            return array_map(fn (mixed $item) => $this->normalize($item), $value);
        }

        return $value;
    }
}
