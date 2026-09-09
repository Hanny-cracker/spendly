<?php

namespace App\Livewire\Transactions;

use App\Actions\Transactions\DeleteTransaction;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Show extends Component
{
    public Transaction $transaction;

    public bool $confirmingDeletion = false;

    public function mount(Transaction $transaction): void
    {
        Gate::authorize('view', $transaction);
        $this->transaction = $transaction->load(['account', 'category', 'recurringTransaction']);
    }

    public function confirmDeletion(): void
    {
        Gate::authorize('delete', $this->transaction);
        abort_if($this->transaction->transfer_id !== null, 403);
        $this->confirmingDeletion = true;
    }

    public function delete(DeleteTransaction $action): mixed
    {
        Gate::authorize('delete', $this->transaction);
        abort_if($this->transaction->transfer_id !== null, 403);
        $action->handle($this->transaction);
        session()->flash('success', 'Transaction deleted successfully.');

        return $this->redirectRoute('transactions', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.transactions.show')->layout('layouts.app', [
            'title' => 'Transaction Details | Spendly',
            'header' => 'Transactions',
        ]);
    }
}
