<?php

namespace App\Services;


use App\Data\Transfer\CreateTransferData;
use App\Models\Account;
use Illuminate\Validation\ValidationException;

class TransferValidationService
{
    public function validate(CreateTransferData $data): void
    {
        $this->validateAccounts($data);
        $this->validateAmount($data);
        $this->validateOwnership($data);
        $this->validateBalance($data);
    }

    protected function validateAccounts(CreateTransferData $data): void
    {
        if ($data->fromAccountId === $data->toAccountId) {
            throw ValidationException::withMessages([
                'to_account' => 'Source and destination accounts cannot be the same.',
            ]);

        }
    }

    protected function validateAmount(CreateTransferData $data): void
    {
        if ($data->amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Transfer amount must be greater than zero.',
            ]);

        }
    }

    protected function validateOwnership(CreateTransferData $data): void
    {
        $count = Account::query()
            ->where('user_id', $data->userId)
            ->whereIn('id', [
                $data->fromAccountId,
                $data->toAccountId,
            ])
            ->count();

        if ($count !== 2) {

            throw ValidationException::withMessages([
                'account' => 'Invalid account selection.',
            ]);

        }
    }

    protected function validateBalance(CreateTransferData $data): void
    {
        $account = Account::findOrFail($data->fromAccountId);
        if ($account->current_balance < $data->amount) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient account balance.',
            ]);
        }
    }
}