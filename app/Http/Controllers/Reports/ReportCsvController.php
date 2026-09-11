<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportDateRangeRequest;
use App\Models\Transaction;
use App\Services\Reports\FinancialReportService;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportCsvController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ReportDateRangeRequest $request, FinancialReportService $service): StreamedResponse
    {
        $range = $request->dateRange();
        $filename = "spendly-transactions-{$range->startDate->toDateString()}-to-{$range->endDate->toDateString()}.csv";

        return response()->streamDownload(function () use ($service, $range): void {
            $output = fopen('php://output', 'wb');

            if ($output === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Date', 'Type', 'Title', 'Description', 'Category', 'Account', 'Amount', 'Currency', 'Status', 'Recurring']);

            $service->transactions($range)->lazy(500)->each(function (Transaction $transaction) use ($output): void {
                fputcsv($output, [$transaction->date->toDateString(), $transaction->type->value, $transaction->title, $transaction->description ?? '', $transaction->category?->name ?? '', $transaction->account->name, $transaction->amount, $transaction->account->currency === 'FCFA' ? 'XAF' : $transaction->account->currency, $transaction->status->value, $transaction->recurring_transaction_id === null ? 'No' : 'Yes']);
            });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
