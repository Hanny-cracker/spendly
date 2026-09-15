<?php

namespace App\Livewire\Transfers;

use App\Actions\Transfers\CreateTransfer;
use App\Data\Transfer\CreateTransferData;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $fromAccountId = '';

    public string $toAccountId = '';

    public string $amount = '';

    public string $date = '';

    public string $description = '';

    public function mount(): void
    {
        abort_unless(Auth::check(), 401);
        $this->date = now()->toDateString();
    }

    public function save(CreateTransfer $action): mixed
    {
        $user = Auth::user();
        $validated = $this->validate([
            'fromAccountId' => ['required', Rule::exists('accounts', 'id')->where('user_id', $user->id)],
            'toAccountId' => ['required', 'different:fromAccountId', Rule::exists('accounts', 'id')->where('user_id', $user->id)],
            'amount' => ['required', 'numeric', 'gt:0'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $action->handle(new CreateTransferData(
            userId: $user->id,
            fromAccountId: (int) $validated['fromAccountId'],
            toAccountId: (int) $validated['toAccountId'],
            amount: (float) $validated['amount'],
            description: $validated['description'] ?: null,
            date: Carbon::parse($validated['date']),
        ));

        session()->flash('success', 'Transfer completed successfully.');

        return $this->redirectRoute('accounts', navigate: true);
    }

    public function render(): View
    {
        $user = Auth::user();

        return view('livewire.transfers.create', [
            'accounts' => Account::query()->where('user_id', $user->id)->orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'Transfer Funds | Spendly', 'header' => 'Accounts']);
    }
}
