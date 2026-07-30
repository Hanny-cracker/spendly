<?php

use App\Actions\Transactions\CreateTransaction;
use App\Actions\Transfers\CreateTransferData;
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

    public function handle(CreateTransferData $data): Transfer
    {
        return DB::transaction(function () use ($data) {

            $this->validation->validate($data);

            $transfer = Transfer::create([

                'user_id' => $data->userId,
                'from_account_id' => $data->fromAccountId,
                'to_account_id' => $data->toAccountId,
                'amount' => $data->amount,
                'description' => $data->description,
                'date' => $data->date,
                'reference' => Str::uuid(),

            ]);

             return $transfer;
        });
    }
}