<?php

it('can update an account', function () {

    $user = $this->createUser();

    $account = $this->createAccount(
        user: $user,
        balance: 1000
    );

    $account->update([
        'name' => 'Main Bank',
    ]);

    expect(
        $account->fresh()->name
    )
        ->toBe('Main Bank');

});
