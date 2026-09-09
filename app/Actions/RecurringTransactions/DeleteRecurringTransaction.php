<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\Models\RecurringTransaction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DeleteRecurringTransaction
{
    public function handle(
        RecurringTransaction $recurringTransaction,
        ?int $userId = null,
    ): bool {

        return DB::transaction(function () use ($recurringTransaction, $userId): bool {
            $recurringTransaction = RecurringTransaction::query()->lockForUpdate()->findOrFail($recurringTransaction->id);
            if ($userId !== null && $recurringTransaction->user_id !== $userId) {
                throw new AuthorizationException;
            }

            return (bool) $recurringTransaction->delete();
        });

    }
}
