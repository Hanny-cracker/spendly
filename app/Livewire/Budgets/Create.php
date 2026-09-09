<?php

namespace App\Livewire\Budgets;

use App\Actions\Budgets\CreateBudget;
use App\Data\Budget\CreateBudgetData;
use App\Enums\BudgetPeriod;
use App\Enums\CategoryType;
use App\Models\Budget;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $categoryId = '';

    public string $amount = '';

    public string $period = 'monthly';

    public string $startDate = '';

    public string $endDate = '';

    public string $alertPercentage = '80';

    public bool $isActive = true;

    public function mount(): void
    {
        Gate::authorize('create', Budget::class);
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->endOfMonth()->toDateString();
    }

    public function save(CreateBudget $action): mixed
    {
        Gate::authorize('create', Budget::class);
        $validated = $this->validate($this->rules());

        $action->handle(new CreateBudgetData(
            userId: auth()->id(), categoryId: (int) $validated['categoryId'], name: $validated['name'], amount: (float) $validated['amount'],
            period: BudgetPeriod::from($validated['period']), startDate: Carbon::parse($validated['startDate']), endDate: Carbon::parse($validated['endDate']),
            alertPercentage: (int) $validated['alertPercentage'], isActive: $validated['isActive'],
        ));

        session()->flash('success', 'Budget created successfully.');

        return $this->redirectRoute('budgets', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.budgets.create', [
            'categories' => Category::query()->where('user_id', auth()->id())->where('type', CategoryType::Expense)->orderBy('name')->get(['id', 'name']),
            'periods' => collect(BudgetPeriod::cases())->mapWithKeys(fn (BudgetPeriod $period) => [$period->value => str($period->value)->replace('_', ' ')->title()->toString()]),
        ])->layout('layouts.app', ['title' => 'Create Budget | Spendly', 'header' => 'Budgets']);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'categoryId' => ['required', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())->where('type', CategoryType::Expense->value))],
            'amount' => ['required', 'numeric', 'gt:0'],
            'period' => ['required', Rule::enum(BudgetPeriod::class)],
            'startDate' => ['required', 'date', 'before_or_equal:endDate'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
            'alertPercentage' => ['required', 'integer', 'between:1,100'],
            'isActive' => ['boolean'],
        ];
    }
}
