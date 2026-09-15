<?php

namespace App\Livewire\Dashboard;

use App\Data\Report\DateRangeData;
use App\Services\AI\AIInsightService;
use App\Services\Dashboard\DashboardService;
use Carbon\Carbon;
use Livewire\Component;

class Index extends Component
{
    public string $startDate;

    public string $endDate;

    /**
     * Livewire-safe dashboard data.
     *
     * @var array<string, mixed>
     */
    public array $dashboardData = [];

    public function mount(
        DashboardService $dashboardService
    ): void {
        $this->startDate = now()
            ->startOfMonth()
            ->toDateString();

        $this->endDate = now()
            ->endOfMonth()
            ->toDateString();

        $this->loadDashboard($dashboardService);
    }

    public function loadDashboard(
        DashboardService $dashboardService
    ): void {
        $user = auth()->guard()->user();

        abort_unless($user, 401);

        $data = new DateRangeData(
            userId: $user->id,
            startDate: Carbon::parse($this->startDate),
            endDate: Carbon::parse($this->endDate),
        );

        $dashboard = $dashboardService->summary($data);

        $this->dashboardData = $dashboard->toArray();
    }

    public function updatedStartDate(
        DashboardService $dashboardService
    ): void {
        $this->loadDashboard($dashboardService);
    }

    public function updatedEndDate(
        DashboardService $dashboardService
    ): void {
        $this->loadDashboard($dashboardService);
    }

    public function render(AIInsightService $insightService)
    {
        return view('livewire.dashboard.index', [
            'aiInsight' => $insightService->latest(auth()->user()),
        ]);
    }
}
