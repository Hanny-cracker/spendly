<?php

namespace App\Livewire\Recurring;

use App\Actions\RecurringTransactions\DeleteRecurringTransaction;
use App\Actions\RecurringTransactions\PauseRecurringTransaction;
use App\Actions\RecurringTransactions\ResumeRecurringTransaction;
use App\Models\RecurringTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public RecurringTransaction $recurringTransaction;

    public bool $confirmingDeletion = false;

    public string $successMessage = '';

    public function mount(RecurringTransaction $recurringTransaction): void
    {
        Gate::authorize('view', $recurringTransaction);
        $this->recurringTransaction = $recurringTransaction;
    }

    public function pause(PauseRecurringTransaction $action): void
    {
        Gate::authorize('update', $this->recurringTransaction);
        abort_unless($this->recurringTransaction->status->isActive(), 422);
        $this->recurringTransaction = $action->handle($this->recurringTransaction, (int) auth()->id());
        $this->successMessage = 'Recurring transaction paused.';
    }

    public function resume(ResumeRecurringTransaction $action): void
    {
        Gate::authorize('update', $this->recurringTransaction);
        abort_unless($this->recurringTransaction->status->isPaused(), 422);
        $this->recurringTransaction = $action->handle($this->recurringTransaction, (int) auth()->id());
        $this->successMessage = 'Recurring transaction resumed.';
    }

    public function delete(DeleteRecurringTransaction $action): mixed
    {
        Gate::authorize('delete', $this->recurringTransaction);
        $action->handle($this->recurringTransaction, (int) auth()->id());
        session()->flash('success', 'Recurring transaction deleted successfully.');

        return $this->redirectRoute('recurring', navigate: true);
    }

    public function render(): View
    {
        Gate::authorize('view', $this->recurringTransaction);
        $history = $this->recurringTransaction->transactions()->where('user_id', auth()->id())->with(['account:id,name,currency', 'category:id,name'])->latest('date')->latest('id')->paginate(10);

        return view('livewire.recurring.show', ['history' => $history])
            ->layout('layouts.app', ['title' => $this->recurringTransaction->title.' | Spendly', 'header' => 'Recurring']);
    }
}
