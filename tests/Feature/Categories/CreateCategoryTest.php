<?php

use App\Actions\Categories\CreateCategory;
use App\Data\Category\CreateCategoryData;
use App\Enums\CategoryType;

it('can create a category for a user', function () {

    $user = $this->createUser();

    $data = new CreateCategoryData(

        userId: $user->id,

        name: 'Food',

        type: CategoryType::Expense

    );

    $category = app(CreateCategory::class)
        ->handle($data);

    expect($category->name)
        ->toBe('Food');

    expect($category->user_id)
        ->toBe($user->id);

});
