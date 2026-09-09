<?php

namespace App\Livewire\Budgets;

use App\Actions\Analysis\BudgetProgressAnalysis;
use App\Actions\Budgets\DeleteBudget;
use App\Actions\Budgets\ToggleBudgetStatus;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Show extends Component
{
    public Budget $budget;

    public bool $confirmingDeletion = false;

    public function mount(Budget $budget): void
    {
        Gate::authorize('view', $budget);
        $this->budget = $budget->load('category');
    }

    public function toggleStatus(ToggleBudgetStatus $action): void
    {
        Gate::authorize('update', $this->budget);
        $this->budget = $action->handle($this->budget)->load('category');
        session()->flash('success', $this->budget->is_active ? 'Budget activated successfully.' : 'Budget deactivated successfully.');
    }

    public function confirmDeletion(): void
    {
        Gate::authorize('delete', $this->budget);
        $this->confirmingDeletion = true;
    }

    public function delete(DeleteBudget $action): mixed
    {
        Gate::authorize('delete', $this->budget);
        $action->handle($this->budget);
        session()->flash('success', 'Budget deleted successfully.');

        return $this->redirectRoute('budgets', navigate: true);
    }

    public function render(BudgetProgressAnalysis $analysis): View
    {
        $progress = $this->budget->is_active
            ? collect($analysis->handle(new DateRangeData(auth()->id(), $this->budget->start_date, $this->budget->end_date)))->firstWhere('budgetId', $this->budget->id)
            : null;

        $spending = Transaction::query()->where('user_id', auth()->id())->where('category_id', $this->budget->category_id)
            ->where('type', TransactionType::Expense)->where('status', TransactionStatus::Completed)
            ->whereBetween('date', [$this->budget->start_date, $this->budget->end_date])->latest('date')->latest('id')->limit(10)->get();

        return view('livewire.budgets.show', ['progress' => $progress, 'spending' => $spending])
            ->layout('layouts.app', ['title' => $this->budget->name.' | Spendly', 'header' => 'Budgets']);
    }
}
