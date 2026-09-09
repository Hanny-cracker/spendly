<?php

namespace App\Actions\Categories;

use App\Models\Category;
use DomainException;
use Illuminate\Support\Facades\DB;

class DeleteCategory
{
    public function handle(Category $category): void
    {

        DB::transaction(function () use ($category): void {
            $category = Category::withoutGlobalScopes()->where('user_id', $category->user_id)->lockForUpdate()->findOrFail($category->id);

            if ($category->transactions()->exists()) {
                throw new DomainException("This category can't be deleted because it has transaction history.");
            }

            if ($category->budgets()->exists()) {
                throw new DomainException("This category can't be deleted because it is used by a budget.");
            }

            if ($category->recurringTransactions()->exists()) {
                throw new DomainException("This category can't be deleted because it is used by a recurring transaction.");
            }

            $category->delete();
        });
    }
}
