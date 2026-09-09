<?php

namespace App\Actions\Transactions;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\AccountService;
use DomainException;
use Illuminate\Support\Facades\DB;

class DeleteTransaction
{
    public function __construct(private AccountService $accountService) {}

    public function handle(Transaction $transaction, bool $allowTransfer = false): void
    {
        if ($transaction->transfer_id !== null && ! $allowTransfer) {
            throw new DomainException('Transfer transactions must be deleted through the transfer workflow.');
        }

        DB::transaction(function () use ($transaction): void {
            $account = Account::withoutGlobalScopes()
                ->where('user_id', $transaction->user_id)
                ->lockForUpdate()
                ->findOrFail($transaction->account_id);

            if ($transaction->status->affectsBalance() && $transaction->type->affectsBalance()) {
                $this->accountService->adjustBalance(
                    account: $account,
                    amount: $transaction->amount,
                    type: $transaction->type,
                    reverse: true,
                );
            }

            $transaction->delete();
        });
    }
}
