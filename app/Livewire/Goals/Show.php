<?php

namespace App\Livewire\Goals;

use App\Actions\Goals\CalculateGoalProgress;
use App\Actions\Goals\CreateGoalContribution;
use App\Actions\Goals\DeleteGoal;
use App\Actions\Goals\DeleteGoalContribution;
use App\Actions\Goals\UpdateGoalContribution;
use App\Data\Goal\CreateGoalContributionData;
use App\Data\Goal\UpdateGoalContributionData;
use App\Models\Account;
use App\Models\Goal;
use App\Models\GoalContribution;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public Goal $goal;

    public bool $showContributionModal = false;

    public ?int $editingContributionId = null;

    public ?int $confirmingRemovalId = null;

    public bool $confirmingGoalDeletion = false;

    public string $amount = '';

    public string $accountId = '';

    public string $contributedAt = '';

    public string $note = '';

    public string $successMessage = '';

    public function mount(Goal $goal): void
    {
        Gate::authorize('view', $goal);
        $this->goal = $goal;
        $this->contributedAt = now()->toDateString();
    }

    public function openContributionModal(): void
    {
        Gate::authorize('update', $this->goal);
        $this->resetValidation();
        $this->editingContributionId = null;
        $this->reset('amount', 'accountId', 'note');
        $this->contributedAt = now()->toDateString();
        $this->showContributionModal = true;
    }

    public function editContribution(int $contributionId): void
    {
        $contribution = $this->ownedContribution($contributionId);
        Gate::authorize('update', $contribution);
        $this->resetValidation();
        $this->editingContributionId = $contribution->id;
        $this->amount = (string) $contribution->amount;
        $this->accountId = $contribution->account_id ? (string) $contribution->account_id : '';
        $this->contributedAt = $contribution->contributed_at->toDateString();
        $this->note = $contribution->note ?? '';
        $this->showContributionModal = true;
    }

    public function addContribution(CreateGoalContribution $action): void
    {
        Gate::authorize('update', $this->goal);
        $validated = $this->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'accountId' => ['nullable', 'integer'],
            'contributedAt' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);
        $action->handle($this->goal, new CreateGoalContributionData(
            userId: (int) auth()->id(),
            amount: (float) $validated['amount'],
            contributedAt: Carbon::parse($validated['contributedAt']),
            accountId: $validated['accountId'] !== null && $validated['accountId'] !== '' ? (int) $validated['accountId'] : null,
            note: $validated['note'] ?: null,
        ));
        $this->goal->refresh();
        $this->reset('amount', 'accountId', 'note', 'showContributionModal');
        $this->contributedAt = now()->toDateString();
        $this->successMessage = 'Contribution added successfully.';
        $this->resetPage();
    }

    public function updateContribution(UpdateGoalContribution $action): void
    {
        abort_unless($this->editingContributionId, 404);
        $contribution = $this->ownedContribution($this->editingContributionId);
        Gate::authorize('update', $contribution);
        $validated = $this->validate($this->contributionRules());
        $action->handle($this->goal, $contribution, new UpdateGoalContributionData(
            userId: (int) auth()->id(), amount: (float) $validated['amount'],
            contributedAt: Carbon::parse($validated['contributedAt']),
            accountId: $validated['accountId'] !== null && $validated['accountId'] !== '' ? (int) $validated['accountId'] : null,
            note: $validated['note'] ?: null,
        ));
        $this->goal->refresh();
        $this->reset('amount', 'accountId', 'note', 'showContributionModal', 'editingContributionId');
        $this->contributedAt = now()->toDateString();
        $this->successMessage = 'Contribution updated successfully.';
    }

    public function confirmContributionRemoval(int $contributionId): void
    {
        $contribution = $this->ownedContribution($contributionId);
        Gate::authorize('delete', $contribution);
        $this->confirmingRemovalId = $contribution->id;
    }

    public function removeContribution(DeleteGoalContribution $action): void
    {
        abort_unless($this->confirmingRemovalId, 404);
        $contribution = $this->ownedContribution($this->confirmingRemovalId);
        Gate::authorize('delete', $contribution);
        $action->handle($this->goal, $contribution, (int) auth()->id());
        $this->goal->refresh();
        $this->confirmingRemovalId = null;
        $this->successMessage = 'Contribution removed successfully.';
    }

    public function deleteGoal(DeleteGoal $action): mixed
    {
        Gate::authorize('delete', $this->goal);
        $action->handle($this->goal, (int) auth()->id());
        session()->flash('success', 'Goal deleted successfully.');

        return $this->redirectRoute('goals', navigate: true);
    }

    public function render(CalculateGoalProgress $calculator): View
    {
        Gate::authorize('view', $this->goal);
        $contributions = $this->goal->contributions()->where('user_id', auth()->id())->with('account:id,name')
            ->orderByDesc('contributed_at')->orderByDesc('id')->paginate(10);
        $accounts = Account::query()->where('user_id', auth()->id())->orderByDesc('is_default')->orderBy('name')->get(['id', 'name']);
        $removingContribution = $this->confirmingRemovalId ? $this->goal->contributions()->where('user_id', auth()->id())->find($this->confirmingRemovalId) : null;

        return view('livewire.goals.show', ['progress' => $calculator->handle($this->goal)->toArray(), 'contributions' => $contributions, 'accounts' => $accounts, 'removingContribution' => $removingContribution])
            ->layout('layouts.app', ['title' => $this->goal->name.' | Spendly', 'header' => 'Goals']);
    }

    private function contributionRules(): array
    {
        return ['amount' => ['required', 'numeric', 'gt:0'], 'accountId' => ['nullable', 'integer'], 'contributedAt' => ['required', 'date', 'before_or_equal:today'], 'note' => ['nullable', 'string', 'max:2000']];
    }

    private function ownedContribution(int $contributionId): GoalContribution
    {
        return $this->goal->contributions()->where('user_id', auth()->id())->findOrFail($contributionId);
    }
}
