<?php

it('creates default categories during onboarding', function () {

    $user = $this->createUser();

    expect(
        $user->categories()->count()
    )
        ->toBeGreaterThan(0);

});
