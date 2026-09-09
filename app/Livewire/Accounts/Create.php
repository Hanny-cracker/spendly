<?php

namespace App\Livewire\Accounts;

use App\Actions\Accounts\CreateAccount;
use App\Data\Account\CreateAccountData;
use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $type = 'cash';

    public string $currency = 'FCFA';

    public string $openingBalance = '0';

    public string $color = '#047857';

    public bool $isDefault = false;

    public function mount(): void
    {
        Gate::authorize('create', Account::class);
        $this->currency = auth()->user()->currency ?? 'FCFA';
    }

    public function save(CreateAccount $action): mixed
    {
        Gate::authorize('create', Account::class);
        $validated = $this->validate($this->rules());

        $account = $action->handle(new CreateAccountData(
            userId: auth()->id(),
            name: $validated['name'],
            type: AccountType::from($validated['type']),
            currency: $validated['currency'],
            openingBalance: (float) $validated['openingBalance'],
            color: $validated['color'],
            isDefault: $validated['isDefault'],
        ));

        session()->flash('success', 'Account created successfully.');

        return $this->redirectRoute('accounts.show', $account, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.accounts.create', $this->formOptions())
            ->layout('layouts.app', ['title' => 'Create Account | Spendly', 'header' => 'Accounts']);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('accounts')->where('user_id', auth()->id())],
            'type' => ['required', Rule::enum(AccountType::class)],
            'currency' => ['required', 'string', 'max:10'],
            'openingBalance' => ['required', 'numeric'],
            'color' => ['required', Rule::in(array_keys($this->colors()))],
            'isDefault' => ['boolean'],
        ];
    }

    private function formOptions(): array
    {
        return ['accountTypes' => AccountType::options(), 'colors' => $this->colors(), 'submitLabel' => 'Create Account'];
    }

    private function colors(): array
    {
        return ['#047857' => 'Emerald', '#2563EB' => 'Blue', '#D97706' => 'Amber', '#7C3AED' => 'Violet', '#E11D48' => 'Rose', '#57534E' => 'Stone'];
    }
}
