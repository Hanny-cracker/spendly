<?php

namespace App\Actions\Accounts;

use App\Data\Account\CreateAccountData;
use App\Enums\Feature;
use App\Models\Account;
use App\Models\User;
use App\Services\Entitlements\EntitlementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateAccount
{
    public function __construct(private EntitlementService $entitlements) {}

    public function handle(
        CreateAccountData $data
    ): Account {

        return DB::transaction(function () use ($data): Account {
            $existing = Account::query()->where('user_id', $data->userId)->where('name', $data->name)->first();
            if ($existing) {
                return $existing;
            }
            $user = User::query()->findOrFail($data->userId);
            $result = $this->entitlements->check($user, Feature::Accounts);
            if (! $result->allowed) {
                throw ValidationException::withMessages(['name' => "You've reached the {$result->limit}-account limit on the Free plan."]);
            }
            if ($data->isDefault) {
                Account::withoutGlobalScopes()
                    ->where('user_id', $data->userId)
                    ->update(['is_default' => false]);
            }

            return Account::firstOrCreate(
                ['user_id' => $data->userId, 'name' => $data->name],
                [
                    'type' => $data->type,
                    'currency' => $data->currency,
                    'opening_balance' => $data->openingBalance,
                    'current_balance' => $data->openingBalance,
                    'color' => $data->color,
                    'is_default' => $data->isDefault,
                ],
            );
        });
    }
}
