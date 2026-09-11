<?php

namespace App\Actions\Categories;

use App\Data\Category\CreateCategoryData;
use App\Models\Category;

class CreateCategory
{
    public function handle(
        CreateCategoryData $data
    ): Category {

        return Category::firstOrCreate(

            [
                'user_id' => $data->userId,
                'name' => $data->name,
                'type' => $data->type,
            ],
            [
                'icon' => $data->icon,
                'color' => $data->color,
            ]
        );
    }
}
