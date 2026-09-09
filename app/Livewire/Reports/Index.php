<?php

namespace App\Livewire\Reports;

use App\Data\Report\DateRangeData;
use App\Services\Reports\FinancialReportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public string $period = 'this_month';

    public string $startDate = '';

    public string $endDate = '';

    /** @var array<string, mixed> */
    public array $reportData = [];

    public function mount(FinancialReportService $service): void
    {
        $this->setPresetDates();
        $this->loadReport($service);
    }

    public function updatedPeriod(FinancialReportService $service): void
    {
        if ($this->period === 'custom') {
            return;
        }
        $this->setPresetDates();
        $this->loadReport($service);
    }

    public function applyCustom(FinancialReportService $service): void
    {
        $this->validate(['startDate' => ['required', 'date', 'before_or_equal:endDate'], 'endDate' => ['required', 'date', 'after_or_equal:startDate']]);
        $this->loadReport($service);
    }

    public function render(): View
    {
        return view('livewire.reports.index')->layout('layouts.app', ['title' => 'Reports | Spendly', 'header' => 'Reports']);
    }

    private function setPresetDates(): void
    {
        [$start, $end] = match ($this->period) {
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'last_three_months' => [now()->subMonthsNoOverflow(2)->startOfMonth(), now()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
        $this->startDate = $start->toDateString();
        $this->endDate = $end->toDateString();
    }

    private function loadReport(FinancialReportService $service): void
    {
        $this->reportData = $service->summary(new DateRangeData(userId: (int) auth()->id(), startDate: Carbon::parse($this->startDate), endDate: Carbon::parse($this->endDate)))->toArray();
    }
}
