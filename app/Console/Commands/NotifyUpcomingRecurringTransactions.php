<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RecurringStatus;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactions\RecurringTransactionNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyUpcomingRecurringTransactions extends Command
{
    protected $signature = 'transactions:notify-upcoming';

    protected $description = 'Notify users about upcoming recurring transactions';

    public function handle(RecurringTransactionNotificationService $notificationService): int
    {
        $scheduleIds = RecurringTransaction::query()
            ->where('status', RecurringStatus::Active)
            ->whereBetween('next_run', [now(), now()->addHours(24)])
            ->pluck('id');

        foreach ($scheduleIds as $scheduleId) {
            DB::transaction(function () use ($scheduleId, $notificationService): void {
                $recurring = RecurringTransaction::query()->lockForUpdate()->find($scheduleId);
                if (! $recurring || ! $recurring->status->isActive()) {
                    return;
                }

                $minutesUntilRun = now()->diffInMinutes($recurring->next_run, false);
                if ($minutesUntilRun <= 0 || $minutesUntilRun > 1440) {
                    return;
                }

                if ($minutesUntilRun <= 360 && $recurring->last_6h_notified_at === null) {
                    $recurring->update(['last_6h_notified_at' => now()]);
                    DB::afterCommit(fn () => $notificationService->upcoming6Hours($recurring->fresh()));

                    return;
                }

                if ($minutesUntilRun > 360 && $recurring->last_24h_notified_at === null && $recurring->last_6h_notified_at === null) {
                    $recurring->update(['last_24h_notified_at' => now()]);
                    DB::afterCommit(fn () => $notificationService->upcoming24Hours($recurring->fresh()));
                }
            });
        }

        return self::SUCCESS;
    }
}
