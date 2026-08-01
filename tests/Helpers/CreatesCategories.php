<?php

namespace Tests\Helpers;

use App\Models\User;
use App\Models\Category;
use App\Enums\CategoryType;

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

                'name'=>$name,
                'type' => $type,

            ], $attributes));

    }
}