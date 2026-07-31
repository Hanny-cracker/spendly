<?php

namespace Tests\Helpers;

use App\Models\User;
use App\Models\Category;
use App\Enums\CategoryType;

trait CreatesCategories
{
    protected function createCategory(
        User $user,
        array $attributes = [],
    ): Category {

        return Category::factory()

            ->for($user)

            ->create(array_merge([

                'name' => 'Food',

                'type' => CategoryType::Expense,

            ], $attributes));

    }
}