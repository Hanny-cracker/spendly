<?php


use App\Models\User;



it('creates default accounts during onboarding', function () {


    $user = User::factory()->create();


    foreach(config('spendly.default_accounts') as $account){


        expect(

            $user->accounts()
                ->where(
                    'name',
                    $account['name']
                )
                ->exists()

        )
        ->toBeTrue();


    }


});

it('marks default accounts correctly', function () {


    $user = $this->createUser();


    $account = $user->accounts()
        ->first();


    expect($account->is_default)
        ->toBeTrue();


});