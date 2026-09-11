<?php

namespace App\Services;

use App\Data\Transfer\CreateTransferData;
use App\Models\Account;
use App\Models\Transfer;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TransferValidationService
{
    public function validate(CreateTransferData $data): void
    {
        $this->validateAccounts($data);
        $this->validateAmount($data);
        $accounts = $this->ownedAccountsForUpdate($data);
        $this->validateBalance($data, $accounts);
    }

    public function validateUpdate(Transfer $transfer, CreateTransferData $data): void
    {
        if ($transfer->user_id !== $data->userId) {
            throw ValidationException::withMessages(['account' => 'Invalid account selection.']);
        }

        $this->validateAccounts($data);
        $this->validateAmount($data);
        $accounts = $this->ownedAccountsForUpdate($data);
        $availableBalance = (float) $accounts->firstWhere('id', $data->fromAccountId)->current_balance;

        if ($transfer->from_account_id === $data->fromAccountId) {
            $availableBalance += (float) $transfer->amount;
        }

        if ($availableBalance < $data->amount) {
            throw ValidationException::withMessages(['amount' => 'Insufficient account balance.']);
        }
    }

    protected function validateAccounts(CreateTransferData $data): void
    {
        if ($data->fromAccountId === $data->toAccountId) {
            throw ValidationException::withMessages(['to_account' => 'Source and destination accounts cannot be the same.']);
        }
    }

    protected function validateAmount(CreateTransferData $data): void
    {
        if ($data->amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Transfer amount must be greater than zero.']);
        }
    }

    /** @return Collection<int, Account> */
    protected function ownedAccountsForUpdate(CreateTransferData $data): Collection
    {
        $accounts = Account::query()
            ->where('user_id', $data->userId)
            ->whereIn('id', [$data->fromAccountId, $data->toAccountId])
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($accounts->count() !== 2) {
            throw ValidationException::withMessages(['account' => 'Invalid account selection.']);
        }

        return $accounts;
    }

    /** @param Collection<int, Account> $accounts */
    protected function validateBalance(CreateTransferData $data, Collection $accounts): void
    {
        $account = $accounts->firstWhere('id', $data->fromAccountId);

        if ($account->current_balance < $data->amount) {
            throw ValidationException::withMessages(['amount' => 'Insufficient account balance.']);
        }
    }
}
