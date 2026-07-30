<?php

namespace App\Data\Category;

use App\Enums\CategoryType;


final readonly class CreateCategoryData
{

    public function __construct(
        public int $userId,
        public string $name,
        public CategoryType $type,
        public ?string $icon = null,
        public ?string $color = null,
    ){}

}