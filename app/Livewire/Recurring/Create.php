<?php

namespace App\Livewire\Recurring;

use App\Actions\RecurringTransactions\CreateRecurringTransaction;
use App\Data\RecurringTransaction\CreateRecurringTransactionData;
use App\Enums\CategoryType;
use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $type = 'expense';

    public string $title = '';

    public string $description = '';

    public string $amount = '';

    public string $accountId = '';

    public string $categoryId = '';

    public string $frequency = 'monthly';

    public string $startDate = '';

    public string $scheduledTime = '08:00';

    public string $endDate = '';

    public function mount(): void
    {
        Gate::authorize('create', RecurringTransaction::class);
        $this->startDate = now()->toDateString();
    }

    public function updatedType(): void
    {
        $this->categoryId = '';
    }

    public function save(CreateRecurringTransaction $action): mixed
    {
        Gate::authorize('create', RecurringTransaction::class);
        $validated = $this->validate($this->rules());
        $startDate = Carbon::parse($validated['startDate']);
        $action->handle(new CreateRecurringTransactionData(
            userId: (int) auth()->id(), accountId: (int) $validated['accountId'], categoryId: (int) $validated['categoryId'],
            title: $validated['title'], description: $validated['description'] ?: null, amount: (float) $validated['amount'],
            type: TransactionType::from($validated['type']), frequency: RecurringFrequency::from($validated['frequency']), interval: 1,
            startDate: $startDate, nextRun: $startDate, endDate: $validated['endDate'] ? Carbon::parse($validated['endDate']) : null,
            status: RecurringStatus::Active, scheduledTime: $validated['scheduledTime'],
        ));
        session()->flash('success', 'Recurring transaction created successfully.');

        return $this->redirectRoute('recurring', navigate: true);
    }

    public function render(): View
    {
        $userId = (int) auth()->id();
        $categoryType = CategoryType::tryFrom($this->type);

        return view('livewire.recurring.create', [
            'accounts' => Account::query()->where('user_id', $userId)->orderByDesc('is_default')->orderBy('name')->get(),
            'categories' => Category::query()->where('user_id', $userId)->when($categoryType, fn ($query) => $query->where('type', $categoryType), fn ($query) => $query->whereRaw('1 = 0'))->orderBy('name')->get(),
            'frequencies' => RecurringFrequency::options(),
        ])->layout('layouts.app', ['title' => 'Create Recurring Transaction | Spendly', 'header' => 'Recurring']);
    }

    private function rules(): array
    {
        $userId = (int) auth()->id();

        return [
            'type' => ['required', Rule::enum(TransactionType::class)->only([TransactionType::Income, TransactionType::Expense])],
            'title' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'accountId' => ['required', Rule::exists('accounts', 'id')->where('user_id', $userId)],
            'categoryId' => ['required', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $userId)->where('type', $this->type))],
            'frequency' => ['required', Rule::enum(RecurringFrequency::class)],
            'startDate' => ['required', 'date', 'after_or_equal:today'],
            'scheduledTime' => ['required', 'date_format:H:i'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
        ];
    }
}
