<?php

namespace App\Actions\Transfers;

use App\Actions\Transactions\UpdateTransaction;
use App\Data\Transfer\CreateTransferData;
use App\Data\Transaction\CreateTransactionData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;

class UpdateTransfer
{
    public function __construct(
        private UpdateTransaction $updateTransaction
    ) {}

    public function handle(
        Transfer $transfer,
        CreateTransferData $data
    ): Transfer {

        return DB::transaction(function () use (
            $transfer,
            $data
        ) {

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

                $transfer->outgoingTransaction,

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
                    status: TransactionStatus::Completed

                )

            );

            //  Update Income Transaction
            

            $this->updateTransaction->handle(

                $transfer->incomingTransaction,

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