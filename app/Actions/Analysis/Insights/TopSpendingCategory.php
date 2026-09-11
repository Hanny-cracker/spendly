<?php

namespace App\Actions\Analysis\Insights;

use App\Data\Analysis\CategoryAnalysisData;

class TopSpendingCategory
{
    /**
     * @param  array<int, CategoryAnalysisData>  $categories
     */
    public function handle(array $categories): ?CategoryAnalysisData
    {
        if (empty($categories)) {
            return null;
        }

        return $categories[0];
    }
}
