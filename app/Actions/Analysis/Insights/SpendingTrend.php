<?php

namespace App\Actions\Analysis\Insights;

use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Carbon\CarbonInterface;

class SpendingTrend
{
    public function handle(DateRangeData $data): array
    {
        $data->validate();

        $currentTotal = $this->total(
            $data->userId,
            $data->startDate,
            $data->endDate
        );

        $days = $data->startDate->diffInDays($data->endDate) + 1;

        $previousEnd = $data->startDate->copy()->subDay();

        $previousStart = $previousEnd
            ->copy()
            ->subDays($days - 1);

        $previousTotal = $this->total(
            $data->userId,
            $previousStart,
            $previousEnd
        );

        $change = $currentTotal - $previousTotal;

        $percentageChange = $previousTotal > 0
            ? ($change / $previousTotal) * 100
            : 0;

        $status = match (true) {
            $percentageChange > 5 => 'increasing',
            $percentageChange < -5 => 'decreasing',
            default => 'stable',
        };

        return [
            'current_total' => $currentTotal,
            'previous_total' => $previousTotal,
            'change' => $change,
            'percentage_change' => $percentageChange,
            'status' => $status,
        ];
    }

    private function total(
        int $userId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): float {
        return (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('type', TransactionType::Expense)
            ->where('status', TransactionStatus::Completed)
            ->whereBetween('date', [
                $startDate,
                $endDate,
            ])
            ->sum('amount');
    }
}