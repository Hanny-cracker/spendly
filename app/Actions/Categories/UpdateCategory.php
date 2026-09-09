<?php

namespace App\Actions\Categories;

use App\Data\Category\UpdateCategoryData;
use App\Models\Category;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCategory
{
    public function handle(
        Category $category,
        UpdateCategoryData $data
    ): Category {

        return DB::transaction(function () use ($category, $data): Category {
            $category = Category::withoutGlobalScopes()->where('user_id', $category->user_id)->lockForUpdate()->findOrFail($category->id);

            if ($category->type !== $data->type && ($category->transactions()->exists() || $category->budgets()->exists() || $category->recurringTransactions()->exists())) {
                throw new DomainException("This category type can't be changed because the category is already used by financial records.");
            }

            if (Category::withoutGlobalScopes()->where('user_id', $category->user_id)->where('type', $data->type)->where('name', $data->name)->whereKeyNot($category->id)->exists()) {
                throw ValidationException::withMessages(['name' => 'A category with this name and type already exists.']);
            }

            $category->update([
                'name' => $data->name,
                'type' => $data->type,
                'icon' => $data->icon,
                'color' => $data->color,
            ]);

            return $category->refresh();
        });
    }
}
