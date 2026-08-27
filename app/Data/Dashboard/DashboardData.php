<?php

namespace App\Data\Dashboard;

use App\Data\Analysis\BudgetProgressData;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

readonly class DashboardData
{
    /**
     * @param array<string, mixed> $reports
     * @param array<string, mixed> $insights
     * @param array<int, BudgetProgressData> $budgets
     * @param array<int, mixed> $recentTransactions
     */
    public function __construct(
        public CarbonInterface $startDate,
        public CarbonInterface $endDate,

        public array $reports,
        public array $insights,
        public array $budgets,
        public array $recentTransactions,
    ) {}

    /**
     * Convert dashboard data to a completely
     * Livewire-safe array.
     */
    public function toArray(): array
    {
        return [
            'start_date' => $this->startDate->toDateString(),

            'end_date' => $this->endDate->toDateString(),

            'reports' => $this->normalize($this->reports),

            'insights' => $this->normalize($this->insights),

            'budgets' => $this->normalize($this->budgets),

            'recent_transactions' => $this->normalize(
                $this->recentTransactions
            ),
        ];
    }

    /**
     * Recursively convert DTOs, models, enums,
     * dates and nested arrays into primitive values.
     */
    private function normalize(mixed $value): mixed
    {
        /*
         * Backed enums
         */
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        /*
         * Dates
         */
        if ($value instanceof CarbonInterface) {
            return $value->toDateString();
        }

        /*
         * Eloquent models
         */
        if ($value instanceof Model) {
            return $this->normalize(
                $value->toArray()
            );
        }

        /*
         * DTOs / objects with toArray()
         */
        if (
            is_object($value)
            && method_exists($value, 'toArray')
        ) {
            return $this->normalize(
                $value->toArray()
            );
        }

        /*
         * Nested arrays
         */
        if (is_array($value)) {
            return array_map(
                fn (mixed $item) => $this->normalize($item),
                $value
            );
        }

        /*
         * Primitive values
         */
        return $value;
    }
}