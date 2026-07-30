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