<?php

namespace App\Actions\Settings;

use App\Actions\Accounts\SetDefaultAccount;
use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\DB;

class UpdateFinancialSettings
{
    public function __construct(private SetDefaultAccount $setDefaultAccount) {}

    public function handle(User $user, ?int $accountId, ?int $expenseCategoryId, ?int $incomeCategoryId): UserPreference
    {
        return DB::transaction(function () use ($user, $accountId, $expenseCategoryId, $incomeCategoryId): UserPreference {
            if ($accountId !== null) {
                $account = Account::query()->where('user_id', $user->id)->findOrFail($accountId);
                $this->setDefaultAccount->handle($account);
            }

            $expenseCategory = $expenseCategoryId === null ? null : Category::query()->where('user_id', $user->id)->where('type', CategoryType::Expense)->findOrFail($expenseCategoryId);
            $incomeCategory = $incomeCategoryId === null ? null : Category::query()->where('user_id', $user->id)->where('type', CategoryType::Income)->findOrFail($incomeCategoryId);

            return UserPreference::query()->updateOrCreate(
                ['user_id' => $user->id],
                ['default_expense_category_id' => $expenseCategory?->id, 'default_income_category_id' => $incomeCategory?->id],
            );
        });
    }
}
