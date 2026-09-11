<?php

namespace App\Actions\Reports;

use App\Data\Report\DateRangeData;
use App\Data\Report\IncomeReportData;
use App\Queries\Reports\TransactionQueries;

class GenerateIncomeReport
{
    public function __construct(
        protected TransactionQueries $queries,
    ) {}

    public function handle(
        DateRangeData $data
    ): IncomeReportData {

        $transactions = $this->queries->income($data);

        $total = (float) $transactions->sum('amount');

        $transactionCount = $transactions->count();

        $average = $transactionCount > 0
            ? $total / $transactionCount
            : 0;

        return new IncomeReportData(
            total: $total,
            transactionCount: $transactionCount,
            average: (float) $average,
        );
    }
}
