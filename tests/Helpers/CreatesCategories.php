<?php

namespace Tests\Helpers;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;

trait CreatesCategories
{
    protected function createCategory(

        User $user,
        string $name = 'Food',
        CategoryType $type = CategoryType::Expense,
        array $attributes = []

    ): Category {

        return Category::factory()
            ->for($user)
            ->create(array_merge([

                'name' => $name,
                'type' => $type,

            ], $attributes));

    }
}
