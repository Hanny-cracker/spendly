<?php

namespace Tests\Helpers;

use App\Models\User;

trait CreatesUsers
{
    protected function createUser(
        array $attributes = []
    ): User {

        return User::factory()
            ->create($attributes);

    }
}
