<?php

namespace App\Actions\Reports;

use App\Data\Report\DateRangeData;
use App\Data\Report\ExpenseReportData;
use App\Queries\Reports\TransactionQueries;

class GenerateExpenseReport
{
    public function __construct(
        protected TransactionQueries $queries,
    ) {}

    public function handle(
        DateRangeData $data
    ): ExpenseReportData {

        $transactions = $this->queries->expenses($data);

        $total = (float) $transactions->sum('amount');

        $transactionCount = $transactions->count();

        $average = $transactionCount > 0
            ? $total / $transactionCount
            : 0;

        return new ExpenseReportData(
            total: $total,
            transactionCount: $transactionCount,
            average: (float) $average,
        );
    }
}