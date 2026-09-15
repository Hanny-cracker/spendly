<?php

namespace App\Livewire\Insights;

use App\Models\AIInsight;
use App\Services\AI\AIInsightService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use RuntimeException;

class Index extends Component
{
    public ?AIInsight $insight = null;

    public ?string $error = null;

    public function mount(AIInsightService $service): void
    {
        $user = Auth::user();
        abort_unless($user, 401);
        $this->insight = $service->latest($user);
    }

    public function regenerate(AIInsightService $service): void
    {
        $user = Auth::user();
        abort_unless($user, 401);
        $this->error = null;

        try {
            $this->insight = $service->generate($user, true);
        } catch (RuntimeException $exception) {
            $this->error = $exception->getMessage();
        }
    }

    public function render(): View
    {
        return view('livewire.insights.index')->layout('layouts.app', ['title' => 'AI Insights | Spendly', 'header' => 'AI Insights']);
    }
}
