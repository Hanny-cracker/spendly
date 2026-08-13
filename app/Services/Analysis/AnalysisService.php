<?php

namespace App\Services\Analysis;

use App\Actions\Analysis\CategoryAnalysis;
use App\Actions\Analysis\CashFlowAnalysis;
use App\Actions\Analysis\IncomeAnalysis;
use App\Actions\Analysis\AnalyzeSpending;
use App\Data\Analysis\CategoryAnalysisData;
use App\Data\Analysis\CashFlowAnalysisData;
use App\Data\Analysis\IncomeAnalysisData;
use App\Data\Analysis\SpendingAnalysisData;
use App\Data\Report\DateRangeData;

class AnalysisService
{
    public function __construct(
        protected AnalyzeSpending $AnalyzeSpending,
        protected IncomeAnalysis $incomeAnalysis,
        protected CashFlowAnalysis $cashFlowAnalysis,
        protected CategoryAnalysis $categoryAnalysis,
    ) {}

    /**
     * Analyze spending.
     */
    public function spending(
        DateRangeData $data
    ): SpendingAnalysisData {
        return $this->AnalyzeSpending->handle($data);
    }

    /**
     * Analyze income.
     */
    public function income(
        DateRangeData $data
    ): IncomeAnalysisData {
        return $this->incomeAnalysis->handle($data);
    }

    /**
     * Analyze cash flow.
     */
    public function cashFlow(
        DateRangeData $data
    ): CashFlowAnalysisData {
        return $this->cashFlowAnalysis->handle($data);
    }

    /**
     * Analyze spending by category.
     *
     * @return array<int, CategoryAnalysisData>
     */
    public function categories(
        DateRangeData $data
    ): array {
        return $this->categoryAnalysis->handle($data);
    }

    /**
     * Generate a complete financial analysis.
     */
    public function summary(
        DateRangeData $data
    ): array {
        return [
            'spending' => $this->spending($data),

            'income' => $this->income($data),

            'cash_flow' => $this->cashFlow($data),

            'categories' => $this->categories($data),
        ];
    }
}