<?php

namespace App\Concerns;

use App\Models\User;

trait ForUser
{
    // abstract public function state(array|callable $attributes): static;

    /**
     * Set the factory state so the created model is assigned to the given user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
        ]);
    }
}
