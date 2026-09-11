<?php

namespace App\Actions\Analysis\Insights;

use App\Data\Analysis\CashFlowAnalysisData;
use App\Data\Report\DateRangeData;

class FinancialHealth
{
    public function __construct(
        protected SavingsRate $savingsRate,
        protected SpendingTrend $spendingTrend,
    ) {}

    /**
     * @param  array{concentration_level?: string}  $concentration
     * @return array<string, mixed>
     */
    public function handle(
        DateRangeData $data,
        CashFlowAnalysisData $cashFlow,
        array $concentration = [],
    ): array {
        $data->validate();

        $savings = $this->savingsRate->handle($data);

        $trend = $this->spendingTrend->handle($data);

        $score = $this->calculateScore(
            savingsRate: $savings['rate'],
            cashFlowStatus: $cashFlow->status,
            spendingTrend: $trend['status'],
            concentrationLevel: $concentration['concentration_level'] ?? 'low',
        );

        return [
            'score' => $score,
            'status' => $this->status($score),
            'income' => $savings['income'],
            'expenses' => $savings['expenses'],
            'savings' => $savings['savings'],
            'savings_rate' => $savings['rate'],
            'cash_flow' => $cashFlow->netCashFlow,
            'cash_flow_status' => $cashFlow->status,
            'spending_trend' => $trend['status'],
            'spending_change_percentage' => $trend['percentage_change'],
            'concentration_level' => $concentration['concentration_level'] ?? 'low',
        ];
    }

    private function calculateScore(
        float $savingsRate,
        string $cashFlowStatus,
        string $spendingTrend,
        string $concentrationLevel,
    ): int {
        $score = 50;

        // Savings rate
        if ($savingsRate >= 30) {
            $score += 25;
        } elseif ($savingsRate >= 20) {
            $score += 15;
        } elseif ($savingsRate >= 10) {
            $score += 5;
        } elseif ($savingsRate > 0) {
            $score += 0;
        } else {
            $score -= 15;
        }

        // Cash flow
        $score += match ($cashFlowStatus) {
            'positive' => 5,
            'neutral' => 0,
            'negative' => -30,
            default => 0,
        };

        // Spending trend
        $score += match ($spendingTrend) {
            'decreasing' => 10,
            'stable' => 5,
            'increasing' => -10,
            default => 0,
        };

        // Spending concentration
        $score += match ($concentrationLevel) {
            'low' => 5,
            'medium' => 0,
            'high' => -5,
            default => 0,
        };

        return max(0, min(100, $score));
    }

    // private function status(int $score): string
    // {
    //     return match (true) {
    //         $score >= 80 => 'excellent',
    //         $score >= 65 => 'good',
    //         $score >= 50 => 'fair',
    //         default => 'poor',
    //     };
    // }
    private function status(int $score): string
    {
        return match (true) {
            $score >= 80 => 'excellent',
            $score >= 65 => 'good',
            $score >= 50 => 'fair',
            default => 'poor',
        };
    }
}
