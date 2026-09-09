<?php

namespace App\Livewire\Transactions;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: '')]
    public string $account = '';

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: '')]
    public string $startDate = '';

    #[Url(except: '')]
    public string $endDate = '';

    public function updated(string $property): void
    {
        if (in_array($property, $this->filterProperties(), true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'type',
            'account',
            'category',
            'startDate',
            'endDate',
        ]);

        $this->resetPage();
    }

    public function render(): View
    {
        $user = Auth::user();

        abort_unless($user, 401);

        $query = $this->filteredQuery($user->id);

        $summary = (clone $query)
            ->whereNull('transfer_id')
            ->withoutEagerLoads()
            ->reorder()
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expenses")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount WHEN type = 'expense' THEN -amount ELSE 0 END), 0) as net")
            ->first();

        return view('livewire.transactions.index', [
            'transactions' => $query->paginate(15),
            'accounts' => Account::query()
                ->where('user_id', $user->id)
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(['id', 'name']),
            'categories' => Category::query()
                ->where('user_id', $user->id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'summary' => [
                'income' => (float) $summary->income,
                'expenses' => (float) $summary->expenses,
                'net' => (float) $summary->net,
            ],
            'hasTransactions' => Transaction::query()
                ->where('user_id', $user->id)
                ->exists(),
            'hasActiveFilters' => $this->hasActiveFilters(),
        ])->layout('layouts.app', [
            'title' => 'Transactions | Spendly',
            'header' => 'Transactions',
        ]);
    }

    private function filteredQuery(int $userId): Builder
    {
        $query = Transaction::query()
            ->where('user_id', $userId)
            ->with([
                'account' => fn ($query) => $query->where('user_id', $userId),
                'category' => fn ($query) => $query->where('user_id', $userId),
            ]);

        $query->when($this->search !== '', function (Builder $query): void {
            $search = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($this->search)).'%';

            $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        });

        if ($this->type !== '') {
            $validTypes = [TransactionType::Income->value, TransactionType::Expense->value];

            $query->whereIn('type', in_array($this->type, $validTypes, true) ? [$this->type] : []);
        }

        if ($this->account !== '') {
            $query->whereHas('account', fn (Builder $query) => $query
                ->where('user_id', $userId)
                ->whereKey($this->account));
        }

        if ($this->category !== '') {
            $query->whereHas('category', fn (Builder $query) => $query
                ->where('user_id', $userId)
                ->whereKey($this->category));
        }

        $query->when($this->startDate !== '', fn (Builder $query) => $query->whereDate('date', '>=', $this->startDate));
        $query->when($this->endDate !== '', fn (Builder $query) => $query->whereDate('date', '<=', $this->endDate));

        return $query
            ->latest('date')
            ->latest('id');
    }

    private function hasActiveFilters(): bool
    {
        foreach ($this->filterProperties() as $property) {
            if ($this->{$property} !== '') {
                return true;
            }
        }

        return false;
    }

    /** @return array<int, string> */
    private function filterProperties(): array
    {
        return ['search', 'type', 'account', 'category', 'startDate', 'endDate'];
    }
}
