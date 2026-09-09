<?php

namespace App\Livewire\Budgets;

use App\Actions\Budgets\UpdateBudget;
use App\Data\Budget\UpdateBudgetData;
use App\Enums\BudgetPeriod;
use App\Enums\CategoryType;
use App\Models\Budget;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public Budget $budget;

    public string $name;

    public string $categoryId;

    public string $amount;

    public string $period;

    public string $startDate;

    public string $endDate;

    public string $alertPercentage;

    public bool $isActive;

    public function mount(Budget $budget): void
    {
        Gate::authorize('update', $budget);
        $this->budget = $budget;
        $this->name = $budget->name;
        $this->categoryId = (string) $budget->category_id;
        $this->amount = (string) $budget->amount;
        $this->period = $budget->period->value;
        $this->startDate = $budget->start_date->toDateString();
        $this->endDate = $budget->end_date->toDateString();
        $this->alertPercentage = (string) $budget->alert_percentage;
        $this->isActive = $budget->is_active;
    }

    public function save(UpdateBudget $action): mixed
    {
        Gate::authorize('update', $this->budget);
        $validated = $this->validate($this->rules());
        $budget = $action->handle($this->budget, new UpdateBudgetData(
            name: $validated['name'], amount: (float) $validated['amount'], alertPercentage: (int) $validated['alertPercentage'], isActive: $validated['isActive'],
            categoryId: (int) $validated['categoryId'], period: BudgetPeriod::from($validated['period']), startDate: Carbon::parse($validated['startDate']), endDate: Carbon::parse($validated['endDate']),
        ));
        session()->flash('success', 'Budget updated successfully.');

        return $this->redirectRoute('budgets.show', $budget, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.budgets.edit', [
            'categories' => Category::query()->where('user_id', auth()->id())->where('type', CategoryType::Expense)->orderBy('name')->get(['id', 'name']),
            'periods' => collect(BudgetPeriod::cases())->mapWithKeys(fn (BudgetPeriod $period) => [$period->value => str($period->value)->title()->toString()]),
        ])->layout('layouts.app', ['title' => 'Edit Budget | Spendly', 'header' => 'Budgets']);
    }

    private function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'categoryId' => ['required', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())->where('type', CategoryType::Expense->value))], 'amount' => ['required', 'numeric', 'gt:0'], 'period' => ['required', Rule::enum(BudgetPeriod::class)], 'startDate' => ['required', 'date', 'before_or_equal:endDate'], 'endDate' => ['required', 'date', 'after_or_equal:startDate'], 'alertPercentage' => ['required', 'integer', 'between:1,100'], 'isActive' => ['boolean']];
    }
}
