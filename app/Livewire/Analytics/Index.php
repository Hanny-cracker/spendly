<?php

namespace App\Livewire\Analytics;

use App\Data\Report\DateRangeData;
use App\Services\Analytics\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Analytics | Spendly', 'header' => 'Analytics'])]
class Index extends Component
{
    public string $startDate;

    public string $endDate;

    /** @var array<string, mixed> */
    public array $analyticsData = [];

    public function mount(AnalyticsService $analyticsService): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->endOfMonth()->toDateString();

        $this->loadAnalytics($analyticsService);
    }

    public function updatedStartDate(AnalyticsService $analyticsService): void
    {
        $this->loadAnalytics($analyticsService);
    }

    public function updatedEndDate(AnalyticsService $analyticsService): void
    {
        $this->loadAnalytics($analyticsService);
    }

    public function loadAnalytics(AnalyticsService $analyticsService): void
    {
        $user = Auth::user();

        abort_unless($user, 401);

        $this->analyticsData = $analyticsService->summary(
            new DateRangeData(
                userId: $user->id,
                startDate: Carbon::parse($this->startDate),
                endDate: Carbon::parse($this->endDate),
            )
        )->toArray();
    }

    public function render(): View
    {
        return view('livewire.analytics.index');
    }
}
