<?php

namespace App\Services\Analysis;

use App\Actions\Analysis\CategoryAnalysis;
use App\Actions\Analysis\CashFlowAnalysis;
use App\Actions\Analysis\IncomeAnalysis;
use App\Actions\Analysis\AnalyzeSpending;
use App\Actions\Analysis\Insights\FinancialHealth;
use App\Actions\Analysis\Insights\SavingsRate;
use App\Actions\Analysis\Insights\SpendingConcentration;
use App\Actions\Analysis\Insights\SpendingTrend;
use App\Data\Report\DateRangeData;

class InsightsService
{
    public function __construct(
        protected AnalyzeSpending $spendingAnalysis,
        protected IncomeAnalysis $incomeAnalysis,
        protected CashFlowAnalysis $cashFlowAnalysis,
        protected CategoryAnalysis $categoryAnalysis,
        protected SavingsRate $savingsRate,
        protected SpendingTrend $spendingTrend,
        protected SpendingConcentration $spendingConcentration,
        protected FinancialHealth $financialHealth,
    ) {}

    /**
     * Generate the complete financial insights.
     */
    public function summary(DateRangeData $data): array
    {
        $data->validate();

        /*
         * Core analysis
         */
        $spending = $this->spendingAnalysis->handle($data);
        $income = $this->incomeAnalysis->handle($data);
        $cashFlow = $this->cashFlowAnalysis->handle($data);
        $categories = $this->categoryAnalysis->handle($data);

        /*
         * Insights
         */
        $savingsRate = $this->savingsRate->handle($data);
        $spendingTrend = $this->spendingTrend->handle($data);
        $spendingConcentration =
            $this->spendingConcentration->handle($categories);

        /*
         * Financial health consumes the
         * already calculated cash-flow result.
         */
        $financialHealth = $this->financialHealth->handle(
            $data,
            $cashFlow,
            $spendingConcentration,
        );

        return [
            'spending' => $spending,
            'income' => $income,
            'cash_flow' => $cashFlow,
            'categories' => $categories,
            'savings_rate' => $savingsRate,
            'spending_trend' => $spendingTrend,
            'spending_concentration' => $spendingConcentration,
            'financial_health' => $financialHealth,
        ];
    }
}
