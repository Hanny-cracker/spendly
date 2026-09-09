<?php

namespace App\Livewire\Transactions;

use App\Actions\Transactions\UpdateTransaction;
use App\Data\Transaction\UpdateTransactionData;
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

class Edit extends Component
{
    public Transaction $transaction;

    public string $type;

    public string $accountId;

    public string $categoryId;

    public string $title;

    public string $description;

    public string $amount;

    public string $date;

    public string $status;

    public function mount(Transaction $transaction): void
    {
        Gate::authorize('update', $transaction);
        abort_if($transaction->transfer_id !== null, 403);

        $this->transaction = $transaction;
        $this->type = $transaction->type->value;
        $this->accountId = (string) $transaction->account_id;
        $this->categoryId = (string) $transaction->category_id;
        $this->title = $transaction->title;
        $this->description = $transaction->description ?? '';
        $this->amount = (string) $transaction->amount;
        $this->date = $transaction->date->toDateString();
        $this->status = $transaction->status->value;
    }

    public function updatedType(): void
    {
        $this->categoryId = '';
    }

    public function save(UpdateTransaction $action): mixed
    {
        Gate::authorize('update', $this->transaction);
        $validated = $this->validate($this->rules());

        $action->handle($this->transaction, new UpdateTransactionData(
            accountId: (int) $validated['accountId'],
            categoryId: (int) $validated['categoryId'],
            title: $validated['title'],
            description: $validated['description'] ?: null,
            transferId: null,
            recurringTransactionId: $this->transaction->recurring_transaction_id,
            amount: (float) $validated['amount'],
            type: TransactionType::from($validated['type']),
            date: Carbon::parse($validated['date']),
            status: TransactionStatus::from($validated['status']),
        ));

        session()->flash('success', 'Transaction updated successfully.');

        return $this->redirectRoute('transactions', navigate: true);
    }

    public function render(BudgetSpendingGuard $budgetSpendingGuard): View
    {
        $userId = auth()->id();
        $categoryType = CategoryType::tryFrom($this->type);

        return view('livewire.transactions.edit', [
            'accounts' => Account::query()->where('user_id', $userId)->orderByDesc('is_default')->orderBy('name')->get(),
            'categories' => Category::query()->where('user_id', $userId)->when($categoryType, fn ($query) => $query->where('type', $categoryType), fn ($query) => $query->whereRaw('1 = 0'))->orderBy('name')->get(),
            'submitLabel' => 'Update Transaction',
            'budgetAvailability' => $this->budgetAvailability($budgetSpendingGuard),
        ])->layout('layouts.app', ['title' => 'Edit Transaction | Spendly', 'header' => 'Transactions']);
    }

    private function budgetAvailability(BudgetSpendingGuard $guard): ?array
    {
        if ($this->type !== TransactionType::Expense->value || $this->status !== TransactionStatus::Completed->value || ! ctype_digit($this->categoryId) || ! Carbon::hasFormat($this->date, 'Y-m-d')) {
            return null;
        }

        return $guard->inspect((int) auth()->id(), (int) $this->categoryId, Carbon::parse($this->date), $this->transaction->id)?->toArray();
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
}
