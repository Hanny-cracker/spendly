<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class UserGrowthChart extends ChartWidget
{
    protected ?string $heading = 'User registrations (last 30 days)';

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();
        $end = now();
        $counts = User::query()->where('created_at', '>=', $start)->selectRaw('DATE(created_at) as day, COUNT(*) as total')->groupBy('day')->pluck('total', 'day');
        $labels = [];
        $data = [];
        for ($date = $start->copy(); $date->lte($end); $date = $date->addDay()) {
            $key = $date->toDateString();
            $labels[] = $date->format('d M');
            $data[] = (int) ($counts[$key] ?? 0);
        }

        return ['datasets' => [['label' => 'Users', 'data' => $data]], 'labels' => $labels];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
