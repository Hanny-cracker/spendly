<?php

namespace App\Actions\Accounts;

use App\Models\Account;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\Transfer;
use DomainException;
use Illuminate\Support\Facades\DB;

class DeleteAccount
{
    public function handle(Account $account): void
    {
        DB::transaction(function () use ($account): void {
            $lockedAccount = Account::withoutGlobalScopes()
                ->where('user_id', $account->user_id)
                ->lockForUpdate()
                ->findOrFail($account->id);

            if (Transaction::withoutGlobalScopes()->where('user_id', $lockedAccount->user_id)->where('account_id', $lockedAccount->id)->exists()) {
                throw new DomainException("This account can't be deleted because it has transaction history.");
            }

            if (Transfer::query()->where('user_id', $lockedAccount->user_id)->where(fn ($query) => $query->where('from_account_id', $lockedAccount->id)->orWhere('to_account_id', $lockedAccount->id))->exists()) {
                throw new DomainException("This account can't be deleted because it is part of a transfer.");
            }

            if (RecurringTransaction::withoutGlobalScopes()->where('user_id', $lockedAccount->user_id)->where('account_id', $lockedAccount->id)->exists()) {
                throw new DomainException("This account can't be deleted because it is used by a recurring transaction.");
            }

            $wasDefault = $lockedAccount->is_default;
            $lockedAccount->delete();

            if ($wasDefault) {
                Account::withoutGlobalScopes()
                    ->where('user_id', $lockedAccount->user_id)
                    ->oldest('id')
                    ->first()
                    ?->update(['is_default' => true]);
            }
        });
    }
}
