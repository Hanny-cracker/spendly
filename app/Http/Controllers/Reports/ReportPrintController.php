<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportDateRangeRequest;
use App\Services\Reports\FinancialReportService;
use Illuminate\Contracts\View\View;

class ReportPrintController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ReportDateRangeRequest $request, FinancialReportService $service): View
    {
        return view('reports.print', [
            'report' => $service->summary($request->dateRange())->toArray(),
            'generatedAt' => now(),
        ]);
    }
}
