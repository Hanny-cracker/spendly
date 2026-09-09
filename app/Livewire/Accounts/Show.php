<?php

namespace App\Livewire\Accounts;

use App\Actions\Accounts\DeleteAccount;
use App\Actions\Accounts\SetDefaultAccount;
use App\Models\Account;
use App\Models\Transaction;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public Account $account;

    public bool $confirmingDeletion = false;

    public ?string $deletionError = null;

    public function mount(Account $account): void
    {
        Gate::authorize('view', $account);
        $this->account = $account;
    }

    public function setDefault(SetDefaultAccount $action): void
    {
        Gate::authorize('update', $this->account);
        $this->account = $action->handle($this->account);
        session()->flash('success', 'Default account updated successfully.');
    }

    public function confirmDeletion(): void
    {
        Gate::authorize('delete', $this->account);
        $this->deletionError = null;
        $this->confirmingDeletion = true;
    }

    public function delete(DeleteAccount $action): mixed
    {
        Gate::authorize('delete', $this->account);

        try {
            $action->handle($this->account);
        } catch (DomainException $exception) {
            $this->deletionError = $exception->getMessage();

            return null;
        }

        session()->flash('success', 'Account deleted successfully.');

        return $this->redirectRoute('accounts', navigate: true);
    }

    public function render(): View
    {
        $transactions = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('account_id', $this->account->id)
            ->with('category')
            ->latest('date')
            ->latest('id')
            ->paginate(10);

        return view('livewire.accounts.show', ['transactions' => $transactions])
            ->layout('layouts.app', ['title' => $this->account->name.' | Spendly', 'header' => 'Accounts']);
    }
}
