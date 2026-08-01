<?php

namespace App\Data\Category;
use App\Enums\CategoryType;


class UpdateCategoryData
{

    public function __construct(

        public string $name,
        public CategoryType $type,
        public ?string $icon = null,
        public ?string $color = null,

    ){}

}