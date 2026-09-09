<?php

namespace App\Livewire\Goals;

use App\Actions\Goals\UpdateGoal;
use App\Data\Goal\UpdateGoalData;
use App\Models\Goal;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Edit extends Component
{
    public Goal $goal;

    public string $name = '';

    public string $targetAmount = '';

    public string $targetDate = '';

    public string $description = '';

    public function mount(Goal $goal): void
    {
        Gate::authorize('update', $goal);
        $this->goal = $goal;
        $this->name = $goal->name;
        $this->targetAmount = (string) $goal->target_amount;
        $this->targetDate = $goal->target_date?->toDateString() ?? '';
        $this->description = $goal->description ?? '';
    }

    public function save(UpdateGoal $action): mixed
    {
        Gate::authorize('update', $this->goal);
        $validated = $this->validate(['name' => ['required', 'string', 'max:255'], 'targetAmount' => ['required', 'numeric', 'gt:0'], 'targetDate' => ['nullable', 'date'], 'description' => ['nullable', 'string', 'max:2000']]);
        $goal = $action->handle($this->goal, new UpdateGoalData(
            userId: (int) auth()->id(),
            name: $validated['name'], targetAmount: (float) $validated['targetAmount'],
            targetDate: $validated['targetDate'] ? Carbon::parse($validated['targetDate']) : null,
            description: $validated['description'] ?: null,
        ));
        session()->flash('success', 'Goal updated successfully.');

        return $this->redirectRoute('goals.show', $goal, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.goals.edit')->layout('layouts.app', ['title' => 'Edit Goal | Spendly', 'header' => 'Goals']);
    }
}
