<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        $user = Auth::user();

        abort_unless($user, 401);

        $accounts = Account::query()
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get()
            ->map(fn (Account $account): array => [
                'public_id' => $account->public_id,
                'name' => $account->name,
                'type' => $account->type->value,
                'type_label' => $account->type->label(),
                'currency' => $account->currency,
                'current_balance' => (float) $account->current_balance,
                'opening_balance' => (float) $account->opening_balance,
                'color' => $this->safeColor($account->color),
                'is_default' => (bool) $account->is_default,
            ])
            ->values();

        return view('livewire.accounts.index', [
            'accounts' => $accounts,
            'summary' => [
                'total_balance' => (float) $accounts->sum('current_balance'),
                'account_count' => $accounts->count(),
            ],
        ])->layout('layouts.app', [
            'title' => 'Accounts | Spendly',
            'header' => 'Accounts',
        ]);
    }

    private function safeColor(?string $color): string
    {
        return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1
            ? $color
            : '#047857';
    }
}
