<?php

namespace App\Actions\Reports;

use App\Data\Report\CashFlowReportData;
use App\Data\Report\DateRangeData;
use App\Queries\Reports\TransactionQueries;

class GenerateCashFlowReport
{
    public function __construct(
        protected TransactionQueries $queries,
    ) {}

    public function handle(
        DateRangeData $data
    ): CashFlowReportData {

        $income = $this->queries
            ->income($data)
            ->sum('amount');

        $expenses = $this->queries
            ->expenses($data)
            ->sum('amount');

        $netCashFlow = $income - $expenses;

        return new CashFlowReportData(
            income: (float) $income,
            expenses: (float) $expenses,
            netCashFlow: (float) $netCashFlow,
        );
    }
}
