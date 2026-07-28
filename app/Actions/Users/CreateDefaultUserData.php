<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateDefaultUserData
{


//This file is used for onboarding / initial setup of a user’s financial data.
// Why it exists:
// To automatically populate a new user’s account with starter data.
// It saves the app from requiring the user to manually create basic categories and accounts first.
// It keeps defaults centralized in spendly.php.
public function handle(User $user): void
{
    // This functiona makes sure that if the defualt data in this function fails it just rolsback rather.
    DB::transaction(function () use ($user) {

        $this->createAccounts($user);

        $this->createCategories($user);

    });
}


    private function createAccounts(User $user): void
    {
        foreach(config('spendly.default_accounts') as $account){

            $user->accounts()->create([
                'name' => $account['name'],
                'type' => $account['type'],
                'currency' => 'USD',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_default' => true,

            ]);
        }
    }


    private function createCategories(User $user): void
    {
        foreach(config('spendly.default_categories') as $type => $categories){

            foreach($categories as $category){
                $user->categories()->create([
                    'name'=>$category,
                    'type'=>$type,
                ]);
            }
        }
    }
}