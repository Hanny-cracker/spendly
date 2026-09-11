<?php

namespace App\Services\Analysis;

use App\Actions\Analysis\AnalyzeSpending;
use App\Data\Analysis\CategorySpendingData;
use App\Data\Analysis\SpendingAnalysisData;
use App\Data\Report\DateRangeData;

class SpendingAnalysisService
{
    public function __construct(
        protected AnalyzeSpending $analyzeSpending,
    ) {}

    /**
     * Get overall spending analysis.
     */
    public function analyze(
        DateRangeData $data
    ): SpendingAnalysisData {

        return $this->analyzeSpending->handle($data);
    }

    /**
     * Get spending analysis by category.
     *
     * @return array<int, CategorySpendingData>
     */
    public function byCategory(
        DateRangeData $data
    ): array {

        return $this->analyzeSpending->byCategory($data);
    }

    /**
     * Get complete spending analysis.
     */
    public function summary(
        DateRangeData $data
    ): array {

        return [
            'overview' => $this->analyze($data),

            'categories' => $this->byCategory($data),
        ];
    }
}
