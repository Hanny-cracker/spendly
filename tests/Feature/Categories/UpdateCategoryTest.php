<?php

use App\Actions\Categories\UpdateCategory;
use App\Data\Category\UpdateCategoryData;
use App\Enums\CategoryType;

it('can update a category', function () {

    $user = $this->createUser();

    $category = $this->createCategory(
        user: $user
    );

    $data = new UpdateCategoryData(
        name: 'Groceries',
        type: CategoryType::Expense
    );

    $updated = app(UpdateCategory::class)
        ->handle(
            $category,
            $data
        );

    expect($updated->name)
        ->toBe('Groceries');

});
