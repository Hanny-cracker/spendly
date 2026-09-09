<?php

namespace App\Livewire\Transactions;

use App\Actions\Transactions\CreateTransaction;
use App\Data\Transaction\CreateTransactionData;
use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\Budgets\BudgetSpendingGuard;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $type = 'expense';

    public string $accountId = '';

    public string $categoryId = '';

    public string $title = '';

    public string $description = '';

    public string $amount = '';

    public string $date = '';

    public string $status = 'completed';

    public function mount(): void
    {
        Gate::authorize('create', Transaction::class);
        $this->date = now()->toDateString();

        $requestedType = TransactionType::tryFrom((string) request()->query('type'));
        if (in_array($requestedType, [TransactionType::Income, TransactionType::Expense], true)) {
            $this->type = $requestedType->value;
        }
    }

    public function updatedType(): void
    {
        $this->categoryId = '';
    }

    public function save(CreateTransaction $action): mixed
    {
        $validated = $this->validate($this->rules());

        $action->handle(new CreateTransactionData(
            userId: auth()->id(),
            accountId: (int) $validated['accountId'],
            categoryId: (int) $validated['categoryId'],
            title: $validated['title'],
            description: $validated['description'] ?: null,
            amount: (float) $validated['amount'],
            type: TransactionType::from($validated['type']),
            status: TransactionStatus::from($validated['status']),
            date: Carbon::parse($validated['date']),
        ));

        session()->flash('success', 'Transaction created successfully.');

        return $this->redirectRoute('transactions', navigate: true);
    }

    public function render(BudgetSpendingGuard $budgetSpendingGuard): View
    {
        return view('livewire.transactions.create', [
            ...$this->formOptions(),
            'budgetAvailability' => $this->budgetAvailability($budgetSpendingGuard),
        ])
            ->layout('layouts.app', ['title' => 'Create Transaction | Spendly', 'header' => 'Transactions']);
    }

    private function budgetAvailability(BudgetSpendingGuard $guard): ?array
    {
        if ($this->type !== TransactionType::Expense->value || $this->status !== TransactionStatus::Completed->value || ! ctype_digit($this->categoryId) || ! Carbon::hasFormat($this->date, 'Y-m-d')) {
            return null;
        }

        return $guard->inspect((int) auth()->id(), (int) $this->categoryId, Carbon::parse($this->date))?->toArray();
    }

    private function rules(): array
    {
        $userId = auth()->id();

        return [
            'type' => ['required', Rule::enum(TransactionType::class)->only([TransactionType::Income, TransactionType::Expense])],
            'accountId' => ['required', Rule::exists('accounts', 'id')->where('user_id', $userId)],
            'categoryId' => ['required', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $userId)->where('type', $this->type))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'date' => ['required', 'date'],
            'status' => ['required', Rule::enum(TransactionStatus::class)],
        ];
    }

    private function formOptions(): array
    {
        $userId = auth()->id();
        $categoryType = CategoryType::tryFrom($this->type);

        return [
            'accounts' => Account::query()->where('user_id', $userId)->orderByDesc('is_default')->orderBy('name')->get(),
            'categories' => Category::query()->where('user_id', $userId)->when($categoryType, fn ($query) => $query->where('type', $categoryType), fn ($query) => $query->whereRaw('1 = 0'))->orderBy('name')->get(),
            'submitLabel' => 'Save Transaction',
        ];
    }
}
