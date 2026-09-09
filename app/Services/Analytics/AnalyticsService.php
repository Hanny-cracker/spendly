<?php

namespace App\Services\Analytics;

use App\Data\Analytics\AnalyticsData;
use App\Data\Report\DateRangeData;
use App\Services\Analysis\InsightsService;
use App\Services\Reports\ReportService;

class AnalyticsService
{
    public function __construct(
        private ReportService $reportService,
        private InsightsService $insightsService,
    ) {}

    public function summary(DateRangeData $data): AnalyticsData
    {
        $data->validate();

        return new AnalyticsData(
            startDate: $data->startDate,
            endDate: $data->endDate,
            reports: $this->reportService->summary($data),
            insights: $this->insightsService->summary($data),
        );
    }
}
