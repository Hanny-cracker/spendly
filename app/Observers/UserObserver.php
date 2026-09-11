<?php

namespace App\Observers;

use App\Actions\Users\CreateDefaultUserData;
use App\Models\User;

class UserObserver
{
    public bool $afterCommit = true;

    public function __construct(
        private CreateDefaultUserData $createDefaultUserData
    ) {}

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->createDefaultUserData
            ->handle($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
