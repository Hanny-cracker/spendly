<?php

namespace App\Http\Controllers\Reports;

use App\Data\Report\DateRangeData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportDateRangeRequest;
use App\Services\Reports\FinancialReportService;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportCsvController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ReportDateRangeRequest $request, FinancialReportService $service): StreamedResponse
    {
        $range = new DateRangeData(
            userId: (int) $request->user()->id,
            startDate: Carbon::parse($request->validated('start_date'))->startOfDay(),
            endDate: Carbon::parse($request->validated('end_date'))->endOfDay(),
        );
        $filename = "spendly-transactions-{$range->startDate->toDateString()}-to-{$range->endDate->toDateString()}.csv";

        return response()->streamDownload(function () use ($service, $range): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Date', 'Public ID', 'Title', 'Type', 'Category', 'Account', 'Amount', 'Currency', 'Status', 'Description']);

            foreach ($service->transactions($range)->lazy(500) as $transaction) {
                fputcsv($output, [$transaction->date->toDateString(), $transaction->public_id, $transaction->title, $transaction->type->value, $transaction->category?->name ?? '', $transaction->account->name, $transaction->amount, $transaction->account->currency === 'FCFA' ? 'XAF' : $transaction->account->currency, $transaction->status->value, $transaction->description ?? '']);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
