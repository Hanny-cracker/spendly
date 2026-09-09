<?php

namespace App\Http\Controllers\Reports;

use App\Data\Report\DateRangeData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportDateRangeRequest;
use App\Services\Reports\FinancialReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ReportPdfController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ReportDateRangeRequest $request, FinancialReportService $service): Response
    {
        $range = new DateRangeData(
            userId: (int) $request->user()->id,
            startDate: Carbon::parse($request->validated('start_date'))->startOfDay(),
            endDate: Carbon::parse($request->validated('end_date'))->endOfDay(),
        );
        $report = $service->summary($range)->toArray();
        $filename = "spendly-report-{$report['start_date']}-to-{$report['end_date']}.pdf";

        return Pdf::loadView('reports.pdf', ['report' => $report])->setPaper('a4')->download($filename);
    }
}
