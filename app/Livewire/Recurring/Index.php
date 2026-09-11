<?php

namespace App\Livewire\Recurring;

use App\Actions\RecurringTransactions\PauseRecurringTransaction;
use App\Actions\RecurringTransactions\ResumeRecurringTransaction;
use App\Enums\RecurringStatus;
use App\Enums\TransactionType;
use App\Models\RecurringTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    public function mount(): void
    {
        Gate::authorize('viewAny', RecurringTransaction::class);
    }

    public function pause(int $scheduleId, PauseRecurringTransaction $action): void
    {
        $schedule = RecurringTransaction::query()->where('user_id', auth()->id())->findOrFail($scheduleId);
        Gate::authorize('update', $schedule);
        $action->handle($schedule, (int) auth()->id());
    }

    public function resume(int $scheduleId, ResumeRecurringTransaction $action): void
    {
        $schedule = RecurringTransaction::query()->where('user_id', auth()->id())->findOrFail($scheduleId);
        Gate::authorize('update', $schedule);
        $action->handle($schedule, (int) auth()->id());
    }

    public function render(): View
    {
        $userId = (int) auth()->id();
        $schedules = RecurringTransaction::query()->where('user_id', $userId)->with(['account:id,name,currency', 'category:id,name'])
            ->orderByRaw("case when status = 'active' then 0 when status = 'paused' then 1 else 2 end")
            ->orderBy('next_run')->get();

        return view('livewire.recurring.index', ['schedules' => $schedules, 'displayTimezone' => auth()->user()->timezone(), 'summary' => [
            'active' => $schedules->where('status', RecurringStatus::Active)->count(),
            'upcoming' => $schedules->where('status', RecurringStatus::Active)->filter(fn (RecurringTransaction $schedule): bool => $schedule->next_run->gte(today()))->count(),
            'expenses' => $schedules->where('type', TransactionType::Expense)->count(),
            'income' => $schedules->where('type', TransactionType::Income)->count(),
        ]])->layout('layouts.app', ['title' => 'Recurring | Spendly', 'header' => 'Recurring']);
    }
}
