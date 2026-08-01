<?php

namespace App\Actions\Categories;

use App\Models\Category;
use Exception;


class DeleteCategory
{

    public function handle(Category $category): void
    {


        if($category->transactions()->exists()){


            throw new Exception(
                'Cannot delete category with transactions'
            );


        }


        $category->delete();


    }

}