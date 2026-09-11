<?php

use App\Actions\Budgets\DeleteBudget;
use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deletes a budget', function () {

    $budget = Budget::factory()->create();

    app(DeleteBudget::class)
        ->handle($budget);

    expect(

        Budget::find($budget->id)

    )->toBeNull();

});
