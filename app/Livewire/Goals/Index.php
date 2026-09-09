<?php

namespace App\Livewire\Goals;

use App\Actions\Goals\CalculateGoalProgress;
use App\Models\Goal;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(CalculateGoalProgress $calculator): View
    {
        $userId = auth()->id();
        abort_unless($userId, 401);
        $goals = Goal::query()->where('user_id', $userId)->orderBy('target_date')->get()
            ->map(fn (Goal $goal): array => $calculator->handle($goal)->toArray())
            ->sortBy(fn (array $goal): int => $goal['status'] === 'active' ? 0 : 1)->values();
        $active = $goals->where('status', 'active');

        return view('livewire.goals.index', ['goals' => $goals, 'summary' => [
            'target' => (float) $goals->sum('target_amount'), 'saved' => (float) $goals->sum('current_amount'),
            'remaining' => (float) $goals->sum('remaining_amount'), 'active_count' => $active->count(),
        ]])->layout('layouts.app', ['title' => 'Goals | Spendly', 'header' => 'Goals']);
    }
}
