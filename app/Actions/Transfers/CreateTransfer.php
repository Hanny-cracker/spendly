<?php

namespace App\Actions\Transfers;

use App\Actions\Transactions\CreateTransaction;
use App\Data\Transaction\CreateTransactionData;
use App\Data\Transfer\CreateTransferData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transfer;
use App\Services\TransferValidationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateTransfer
{
    public function __construct(
        private TransferValidationService $validation,
        private CreateTransaction $createTransaction,
    ) {}

    public function handle(
        CreateTransferData $data
    ): Transfer {

        return DB::transaction(function () use ($data) {

            $this->validation->validate($data);

            $transfer = Transfer::create([
                'user_id' => $data->userId,
                'from_account_id' => $data->fromAccountId,
                'to_account_id' => $data->toAccountId,
                'amount' => $data->amount,
                'description' => $data->description,
                'date' => $data->date,
                'reference' => (string) Str::uuid(),

            ]);

            //  Debit Source Account

            $this->createTransaction->handle(
                new CreateTransactionData(
                    userId: $data->userId,
                    accountId: $data->fromAccountId,
                    categoryId: null,
                    transferId: $transfer->id,
                    recurringTransactionId: null,
                    title: 'Transfer to '.$transfer->toAccount->name,
                    description: $data->description,
                    amount: $data->amount,
                    type: TransactionType::Expense,
                    date: $data->date,
                    status: TransactionStatus::Completed,
                )

            );

            //   Credit Destination Account

            $this->createTransaction->handle(

                new CreateTransactionData(
                    userId: $data->userId,
                    accountId: $data->toAccountId,
                    categoryId: null,
                    transferId: $transfer->id,
                    recurringTransactionId: null,
                    title: 'Transfer from '.$transfer->fromAccount->name,
                    description: $data->description,
                    amount: $data->amount,
                    type: TransactionType::Income,
                    date: $data->date,
                    status: TransactionStatus::Completed,
                )

            );

            return $transfer->fresh([
                'fromAccount',
                'toAccount',
                'transactions',
            ]);
        });
    }
}
