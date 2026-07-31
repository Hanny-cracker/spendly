<?php

use App\Models\User;


test('new user receives default accounts and categories', function () {


    // Arrange
    $user = User::factory()->create();


    // Assert accounts
    expect(
        $user->accounts()->count()
    )->toBeGreaterThan(0);



    // Assert categories

    expect(
        $user->categories()->count()
    )->toBeGreaterThan(0);
});


it('creates the configured default accounts', function () {

    $user = User::factory()->create();

    foreach (config('spendly.default_accounts') as $account) {

        expect(
            $user->accounts()
                ->where('name', $account['name'])
                ->exists()
        )->toBeTrue();
    }
});


it('creates the configured default categories', function () {

    $user = User::factory()->create();

    foreach (config('spendly.default_categories') as $type => $categories) {

        foreach ($categories as $category) {

            expect(
                $user->categories()
                    ->where('type', $type)
                    ->where('name', $category)
                    ->exists()
            )->toBeTrue();
        }
    }
});

it('does not create duplicate onboarding data', function () {

    $user = User::factory()->create();

    app(\App\Actions\Users\CreateDefaultUserData::class)
        ->handle($user);

    expect(
        $user->accounts()->count()
    )->toBe(count(config('spendly.default_accounts')));

    $expected = collect(config('spendly.default_categories'))
        ->flatten()
        ->count();

    expect(
        $user->categories()->count()
    )->toBe($expected);

});