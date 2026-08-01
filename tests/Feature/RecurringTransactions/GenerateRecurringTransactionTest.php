<?php
use App\Models\User;
use App\Services\RecurringTransactionService;
use App\Models\RecurringTransaction;

it('generates a recurring transaction', function(){

    $user = User::factory()->create();

    $recurring =
        RecurringTransaction::factory()
        ->for($user)
        ->create([
            'next_run'=>today(),
        ]);


    app(RecurringTransactionService::class)
        ->generate($recurring);



    expect(
        $user->transactions()->count()
    )
    ->toBe(1);


});