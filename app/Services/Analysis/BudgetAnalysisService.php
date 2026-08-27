<?php

namespace App\Services\Analysis;

use App\Actions\Analysis\BudgetProgressAnalysis;
use App\Data\Analysis\BudgetProgressData;
use App\Data\Report\DateRangeData;

class BudgetAnalysisService
{
    public function __construct(
        protected BudgetProgressAnalysis $budgetProgressAnalysis,
    ) {}

    /**
     * Analyze budget progress.
     *
     * @return array<int, BudgetProgressData>
     */
    public function progress(
        DateRangeData $data
    ): array {
        return $this->budgetProgressAnalysis->handle($data);
    }
}