<?php

namespace App\Livewire\Recurring;

use App\Actions\RecurringTransactions\UpdateRecurringTransaction;
use App\Data\RecurringTransaction\UpdateRecurringTransactionData;
use App\Enums\CategoryType;
use App\Enums\RecurringFrequency;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public RecurringTransaction $recurringTransaction;

    public string $type = '';

    public string $title = '';

    public string $description = '';

    public string $amount = '';

    public string $accountId = '';

    public string $categoryId = '';

    public string $frequency = '';

    public string $startDate = '';

    public string $scheduledTime = '';

    public string $endDate = '';

    public bool $hasHistory = false;

    public function mount(RecurringTransaction $recurringTransaction): void
    {
        Gate::authorize('update', $recurringTransaction);
        $this->recurringTransaction = $recurringTransaction;
        $this->type = $recurringTransaction->type->value;
        $this->title = $recurringTransaction->title;
        $this->description = $recurringTransaction->description ?? '';
        $this->amount = (string) $recurringTransaction->amount;
        $this->accountId = (string) $recurringTransaction->account_id;
        $this->categoryId = (string) $recurringTransaction->category_id;
        $this->frequency = $recurringTransaction->frequency->value;
        $this->startDate = $recurringTransaction->start_date->toDateString();
        $this->scheduledTime = substr((string) $recurringTransaction->scheduled_time, 0, 5);
        $this->endDate = $recurringTransaction->end_date?->toDateString() ?? '';
        $this->hasHistory = $recurringTransaction->transactions()->exists();
    }

    public function updatedType(): void
    {
        $this->categoryId = '';
    }

    public function save(UpdateRecurringTransaction $action): mixed
    {
        Gate::authorize('update', $this->recurringTransaction);
        $validated = $this->validate($this->rules());
        $recurring = $action->handle($this->recurringTransaction, new UpdateRecurringTransactionData(
            title: $validated['title'], description: $validated['description'] ?: null, amount: (float) $validated['amount'],
            type: TransactionType::from($validated['type']), frequency: RecurringFrequency::from($validated['frequency']), interval: 1,
            endDate: $validated['endDate'] ? Carbon::parse($validated['endDate']) : null, status: $this->recurringTransaction->status,
            userId: (int) auth()->id(), accountId: (int) $validated['accountId'], categoryId: (int) $validated['categoryId'], startDate: Carbon::parse($validated['startDate']), scheduledTime: $validated['scheduledTime'],
        ));
        session()->flash('success', 'Recurring transaction updated successfully.');

        return $this->redirectRoute('recurring.show', $recurring, navigate: true);
    }

    public function render(): View
    {
        $userId = (int) auth()->id();
        $categoryType = CategoryType::tryFrom($this->type);

        return view('livewire.recurring.edit', ['accounts' => Account::query()->where('user_id', $userId)->orderByDesc('is_default')->orderBy('name')->get(), 'categories' => Category::query()->where('user_id', $userId)->when($categoryType, fn ($query) => $query->where('type', $categoryType), fn ($query) => $query->whereRaw('1=0'))->orderBy('name')->get(), 'frequencies' => RecurringFrequency::options()])
            ->layout('layouts.app', ['title' => 'Edit Recurring Transaction | Spendly', 'header' => 'Recurring']);
    }

    private function rules(): array
    {
        $userId = (int) auth()->id();

        return ['type' => ['required', Rule::enum(TransactionType::class)->only([TransactionType::Income, TransactionType::Expense])], 'title' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:2000'], 'amount' => ['required', 'numeric', 'gt:0'], 'accountId' => ['required', Rule::exists('accounts', 'id')->where('user_id', $userId)], 'categoryId' => ['required', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $userId)->where('type', $this->type))], 'frequency' => ['required', Rule::enum(RecurringFrequency::class)], 'startDate' => ['required', 'date'], 'scheduledTime' => ['required', 'date_format:H:i'], 'endDate' => ['nullable', 'date', 'after_or_equal:startDate']];
    }
}
