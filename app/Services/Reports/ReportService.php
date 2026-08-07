<?php

namespace App\Services\Reports;

use App\Actions\Reports\GenerateCategoryReport;
use App\Actions\Reports\GenerateCashFlowReport;
use App\Actions\Reports\GenerateExpenseReport;
use App\Actions\Reports\GenerateIncomeReport;
use App\Data\Report\CategoryReportData;
use App\Data\Report\CashFlowReportData;
use App\Data\Report\DateRangeData;
use App\Data\Report\ExpenseReportData;
use App\Data\Report\IncomeReportData;

class ReportService
{
    public function __construct(
        protected GenerateExpenseReport $expenseReport,
        protected GenerateIncomeReport $incomeReport,
        protected GenerateCashFlowReport $cashFlowReport,
        protected GenerateCategoryReport $categoryReport,
    ) {}

    /**
     * Generate an expense report.
     */
    public function expense(
        DateRangeData $data
    ): ExpenseReportData {
        return $this->expenseReport->handle($data);
    }

    /**
     * Generate an income report.
     */
    public function income(
        DateRangeData $data
    ): IncomeReportData {
        return $this->incomeReport->handle($data);
    }

    /**
     * Generate a cash-flow report.
     */
    public function cashFlow(
        DateRangeData $data
    ): CashFlowReportData {
        return $this->cashFlowReport->handle($data);
    }

    /**
     * Generate a category report.
     *
     * @return array<int, CategoryReportData>
     */
    public function byCategory(
        DateRangeData $data
    ): array {
        return $this->categoryReport->handle($data);
    }

    /**
     * Generate all reports for the given date range.
     */
    public function summary(
        DateRangeData $data
    ): array {
        return [
            'expense' => $this->expense($data),
            'income' => $this->income($data),
            'cash_flow' => $this->cashFlow($data),
            'categories' => $this->byCategory($data),
        ];
    }
}