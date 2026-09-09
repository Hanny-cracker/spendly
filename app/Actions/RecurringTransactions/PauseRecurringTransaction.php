<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\Enums\RecurringStatus;
use App\Models\RecurringTransaction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class PauseRecurringTransaction
{
    public function handle(
        RecurringTransaction $recurringTransaction,
        ?int $userId = null,
    ): RecurringTransaction {

        return DB::transaction(function () use ($recurringTransaction, $userId) {
            if ($userId !== null && $recurringTransaction->user_id !== $userId) {
                throw new AuthorizationException;
            }

            $recurringTransaction->update([
                'status' => RecurringStatus::Paused,
            ]);

            return $recurringTransaction->refresh();

        });

    }
}
