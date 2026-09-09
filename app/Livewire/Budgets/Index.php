<?php

namespace App\Livewire\Budgets;

use App\Actions\Analysis\BudgetProgressAnalysis;
use App\Actions\Budgets\DeleteBudget;
use App\Actions\Budgets\ToggleBudgetStatus;
use App\Data\Report\DateRangeData;
use App\Models\Budget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    public ?string $deletingBudget = null;

    public function toggleStatus(string $publicId, ToggleBudgetStatus $action): void
    {
        $budget = $this->ownedBudget($publicId);
        Gate::authorize('update', $budget);
        $action->handle($budget);
    }

    public function confirmDeletion(string $publicId): void
    {
        $budget = $this->ownedBudget($publicId);
        Gate::authorize('delete', $budget);
        $this->deletingBudget = $publicId;
    }

    public function delete(DeleteBudget $action): void
    {
        abort_unless($this->deletingBudget, 404);
        $budget = $this->ownedBudget($this->deletingBudget);
        Gate::authorize('delete', $budget);
        $action->handle($budget);
        $this->deletingBudget = null;
        session()->flash('success', 'Budget deleted successfully.');
    }

    public function render(BudgetProgressAnalysis $analysis): View
    {
        $userId = auth()->id();
        abort_unless($userId, 401);

        $budgets = Budget::query()->where('user_id', $userId)->with('category')->orderByDesc('is_active')->latest('start_date')->get();
        $activeBudgets = $budgets->where('is_active', true);
        $progress = [];

        if ($activeBudgets->isNotEmpty()) {
            $progress = collect($analysis->handle(new DateRangeData(
                userId: $userId,
                startDate: $activeBudgets->min('start_date'),
                endDate: $activeBudgets->max('end_date'),
            )))->keyBy('budgetId');
        }

        $cards = $budgets->map(function (Budget $budget) use ($progress): array {
            $item = $progress[$budget->id] ?? null;

            return [
                'name' => $budget->name,
                'public_id' => $budget->public_id,
                'category' => $budget->category?->name ?? 'Uncategorized',
                'period' => str($budget->period->value)->replace('_', ' ')->title()->toString(),
                'start_date' => $budget->start_date->format('d M Y'),
                'end_date' => $budget->end_date->format('d M Y'),
                'amount' => (float) $budget->amount,
                'spent' => $item?->spentAmount ?? 0.0,
                'remaining' => $item?->remainingAmount ?? (float) $budget->amount,
                'percentage' => $item?->percentageUsed ?? 0.0,
                'status' => $budget->is_active ? ($item?->status ?? 'on_track') : 'inactive',
                'is_active' => $budget->is_active,
            ];
        });

        $activeCards = $cards->where('is_active', true);

        return view('livewire.budgets.index', [
            'budgets' => $cards,
            'summary' => [
                'total_budget' => (float) $activeCards->sum('amount'),
                'total_spent' => (float) $activeCards->sum('spent'),
                'remaining' => (float) $activeCards->sum('remaining'),
                'active_count' => $activeCards->count(),
            ],
        ])->layout('layouts.app', ['title' => 'Budgets | Spendly', 'header' => 'Budgets']);
    }

    private function ownedBudget(string $publicId): Budget
    {
        return Budget::query()->where('user_id', auth()->id())->where('public_id', $publicId)->firstOrFail();
    }
}
