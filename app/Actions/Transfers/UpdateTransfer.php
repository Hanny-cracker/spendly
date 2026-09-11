<?php

namespace App\Actions\Transfers;

use App\Actions\Transactions\UpdateTransaction;
use App\Data\Transaction\UpdateTransactionData;
use App\Data\Transfer\CreateTransferData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Services\TransferValidationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateTransfer
{
    public function __construct(
        private UpdateTransaction $updateTransaction,
        private TransferValidationService $validation,
    ) {}

    public function handle(
        Transfer $transfer,
        CreateTransferData $data
    ): Transfer {

        return DB::transaction(function () use (
            $transfer,
            $data
        ) {

            $this->validation->validateUpdate($transfer, $data);

            $outgoingTransaction = $transfer->transactions()
                ->where('type', TransactionType::Expense)
                ->first();
            $incomingTransaction = $transfer->transactions()
                ->where('type', TransactionType::Income)
                ->first();

            if (! $outgoingTransaction instanceof Transaction || ! $incomingTransaction instanceof Transaction) {
                throw ValidationException::withMessages([
                    'transfer' => 'This transfer is incomplete and cannot be updated.',
                ]);
            }

            $accounts = Account::query()
                ->where('user_id', $data->userId)
                ->whereIn('id', [$data->fromAccountId, $data->toAccountId])
                ->get()
                ->keyBy('id');
            $fromAccount = $accounts->get($data->fromAccountId);
            $toAccount = $accounts->get($data->toAccountId);

            if (! $fromAccount instanceof Account || ! $toAccount instanceof Account) {
                throw ValidationException::withMessages(['account' => 'Invalid account selection.']);
            }

            //    Update Transfer

            $transfer->update([
                'from_account_id' => $data->fromAccountId,
                'to_account_id' => $data->toAccountId,
                'amount' => $data->amount,
                'description' => $data->description,
                'date' => $data->date,
            ]);

            //  Update Expense Transaction

            $this->updateTransaction->handle(

                $outgoingTransaction,

                new UpdateTransactionData(
                    accountId: $data->fromAccountId,
                    categoryId: null,
                    transferId: $transfer->id,
                    recurringTransactionId: null,
                    title: 'Transfer to '.$toAccount->name,
                    description: $data->description,
                    amount: $data->amount,
                    type: TransactionType::Expense,
                    date: $data->date,
                    status: TransactionStatus::Completed

                )

            );

            //  Update Income Transaction

            $this->updateTransaction->handle(

                $incomingTransaction,

                new UpdateTransactionData(
                    accountId: $data->toAccountId,
                    categoryId: null,
                    transferId: $transfer->id,
                    recurringTransactionId: null,
                    title: 'Transfer from '.$fromAccount->name,
                    description: $data->description,
                    amount: $data->amount,
                    type: TransactionType::Income,
                    date: $data->date,
                    status: TransactionStatus::Completed
                )

            );

            return $transfer->fresh([
                'fromAccount',
                'toAccount',
                'outgoingTransaction',
                'incomingTransaction',
            ]);

        });

    }
}
