<?php

namespace App\Actions\Users;

use App\Actions\Accounts\CreateAccount;
use App\Actions\Categories\CreateCategory;
use App\Data\Account\CreateAccountData;
use App\Data\Category\CreateCategoryData;
use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateDefaultUserData
{
    public function __construct(
        private CreateAccount $createAccount,
        private CreateCategory $createCategory,
    ) {}

    /**
     * Create the initial financial setup
     * for a new user.
     */
    public function handle(User $user): void
    {

        DB::transaction(function () use ($user) {
            $this->createAccounts($user);
            $this->createCategories($user);
        });
    }

    private function createAccounts(User $user): void
    {

        foreach (config('spendly.default_accounts') as $index => $account) {

            $this->createAccount->handle(
                new CreateAccountData(
                    userId: $user->id,
                    name: $account['name'],
                    type: AccountType::from(
                        $account['type']
                    ),
                    currency: $user->currency ?? 'FCFA',
                    openingBalance: 0,
                    isDefault: $index === 0,
                )

            );
        }
    }

    private function createCategories(User $user): void
    {

        foreach (
            config('spendly.default_categories') as $type => $categories
        ) {

            foreach ($categories as $category) {
                $this->createCategory->handle(
                    new CreateCategoryData(
                        userId: $user->id,
                        name: $category,
                        type: CategoryType::from($type)
                    )

                );
            }
        }
    }
}
