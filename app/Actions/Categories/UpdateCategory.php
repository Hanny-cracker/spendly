<?php

namespace App\Actions\Categories;


use App\Data\Category\UpdateCategoryData;
use App\Models\Category;



class UpdateCategory
{

    public function handle(
        Category $category,
        UpdateCategoryData $data
    ): Category {


        $category->update(  
            [
                'name' => $data->name,
                'type' => $data->type,
            ],
            [
                'icon' => $data->icon,
                'color' => $data->color,
            ]);


        return $category->refresh();

    }

}