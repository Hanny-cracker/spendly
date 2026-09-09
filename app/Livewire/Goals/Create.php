<?php

namespace App\Livewire\Goals;

use App\Actions\Goals\CreateGoal;
use App\Data\Goal\CreateGoalData;
use App\Models\Goal;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $targetAmount = '';

    public string $targetDate = '';

    public string $description = '';

    public function mount(): void
    {
        Gate::authorize('create', Goal::class);
    }

    public function save(CreateGoal $action): mixed
    {
        Gate::authorize('create', Goal::class);
        $validated = $this->validate(['name' => ['required', 'string', 'max:255'], 'targetAmount' => ['required', 'numeric', 'gt:0'], 'targetDate' => ['nullable', 'date', 'after_or_equal:today'], 'description' => ['nullable', 'string', 'max:2000']]);
        $action->handle(new CreateGoalData(
            userId: auth()->id(), name: $validated['name'], targetAmount: (float) $validated['targetAmount'],
            targetDate: $validated['targetDate'] ? Carbon::parse($validated['targetDate']) : null, description: $validated['description'] ?: null,
        ));
        session()->flash('success', 'Goal created successfully.');

        return $this->redirectRoute('goals', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.goals.create')->layout('layouts.app', ['title' => 'Create Goal | Spendly', 'header' => 'Goals']);
    }
}
