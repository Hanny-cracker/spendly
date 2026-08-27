<?php

namespace App\Services\Dashboard;

use App\Data\Dashboard\DashboardData;
use App\Data\Report\DateRangeData;
use App\Services\Reports\ReportService;
use App\Services\Analysis\InsightsService;
use App\Services\Analysis\BudgetAnalysisService;
use App\Models\Transaction;

class DashboardService
{
    public function __construct(
        protected ReportService $reportService,
        protected InsightsService $insightsService,
        protected BudgetAnalysisService $budgetAnalysisService,
    ) {}

    /**
     * Generate the complete dashboard data.
     */
    public function summary(
        DateRangeData $data
    ): DashboardData {

        $data->validate();

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        $reports = $this->reportService->summary($data);

        /*
        |--------------------------------------------------------------------------
        | Analysis & Insights
        |--------------------------------------------------------------------------
        */

        $insights = $this->insightsService->summary($data);

        /*
        |--------------------------------------------------------------------------
        | Budget Progress
        |--------------------------------------------------------------------------
        */

        $budgets = $this->budgetAnalysisService->progress($data);

        /*
        |--------------------------------------------------------------------------
        | Recent Transactions
        |--------------------------------------------------------------------------
        |
        | Recent transactions are intentionally independent
        | of the selected report period.
        |
        */

        $recentTransactions = Transaction::query()
            ->where('user_id', $data->userId)
            ->with(['category', 'account'])
            ->latest('date')
            ->latest('id')
            ->limit(10)
            ->get()
            ->all();

        return new DashboardData(
            startDate: $data->startDate,
            endDate: $data->endDate,
            reports: $reports,
            insights: $insights,
            budgets: $budgets,
            recentTransactions: $recentTransactions,
        );
    }
}