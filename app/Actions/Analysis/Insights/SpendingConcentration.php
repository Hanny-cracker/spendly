<?php

namespace App\Actions\Analysis\Insights;

use App\Data\Analysis\CategoryAnalysisData;

class SpendingConcentration
{
    /**
     * @param array<int, CategoryAnalysisData> $categories
     */
    public function handle(array $categories): array
    {
        if (empty($categories)) {
            return [
                'total' => 0.0,
                'top_category' => null,
                'top_category_percentage' => 0.0,
                'top_two_percentage' => 0.0,
                'top_three_percentage' => 0.0,
                'concentration_level' => 'low',
            ];
        }

        $total = array_sum(
            array_map(
                fn (CategoryAnalysisData $category) => $category->total,
                $categories
            )
        );

        if ($total <= 0) {
            return [
                'total' => 0.0,
                'top_category' => null,
                'top_category_percentage' => 0.0,
                'top_two_percentage' => 0.0,
                'top_three_percentage' => 0.0,
                'concentration_level' => 'low',
            ];
        }

        $topCategory = $categories[0];

        $topTwo = array_slice($categories, 0, 2);

        $topThree = array_slice($categories, 0, 3);

        $topTwoTotal = array_sum(
            array_map(
                fn (CategoryAnalysisData $category) => $category->total,
                $topTwo
            )
        );

        $topThreeTotal = array_sum(
            array_map(
                fn (CategoryAnalysisData $category) => $category->total,
                $topThree
            )
        );

        $topCategoryPercentage =
            ($topCategory->total / $total) * 100;

        $topTwoPercentage =
            ($topTwoTotal / $total) * 100;

        $topThreePercentage =
            ($topThreeTotal / $total) * 100;

        $concentrationLevel = match (true) {
            $topThreePercentage >= 75 => 'high',
            $topThreePercentage >= 50 => 'medium',
            default => 'low',
        };

        return [
            'total' => (float) $total,
            'top_category' => $topCategory,
            'top_category_percentage' =>
                (float) $topCategoryPercentage,
            'top_two_percentage' =>
                (float) $topTwoPercentage,
            'top_three_percentage' =>
                (float) $topThreePercentage,
            'concentration_level' =>
                $concentrationLevel,
        ];
    }
}