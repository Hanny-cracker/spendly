<?php

namespace App\Livewire\Accounts;

use App\Actions\Accounts\UpdateAccount;
use App\Data\Account\UpdateAccountData;
use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public Account $account;

    public string $name;

    public string $type;

    public string $currency;

    public string $color;

    public bool $isDefault;

    public function mount(Account $account): void
    {
        Gate::authorize('update', $account);
        $this->account = $account;
        $this->name = $account->name;
        $this->type = $account->type->value;
        $this->currency = $account->currency;
        $this->color = $this->safeColor($account->color);
        $this->isDefault = $account->is_default;
    }

    public function save(UpdateAccount $action): mixed
    {
        Gate::authorize('update', $this->account);
        $validated = $this->validate($this->rules());
        $account = $action->handle($this->account, new UpdateAccountData(
            name: $validated['name'],
            type: AccountType::from($validated['type']),
            currency: $validated['currency'],
            color: $validated['color'],
            isDefault: $validated['isDefault'],
        ));
        session()->flash('success', 'Account updated successfully.');

        return $this->redirectRoute('accounts.show', $account, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.accounts.edit', ['accountTypes' => AccountType::options(), 'colors' => $this->colors(), 'submitLabel' => 'Update Account'])
            ->layout('layouts.app', ['title' => 'Edit Account | Spendly', 'header' => 'Accounts']);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('accounts')->where('user_id', auth()->id())->ignore($this->account)],
            'type' => ['required', Rule::enum(AccountType::class)],
            'currency' => ['required', 'string', 'max:10'],
            'color' => ['required', Rule::in(array_keys($this->colors()))],
            'isDefault' => ['boolean'],
        ];
    }

    private function colors(): array
    {
        return ['#047857' => 'Emerald', '#2563EB' => 'Blue', '#D97706' => 'Amber', '#7C3AED' => 'Violet', '#E11D48' => 'Rose', '#57534E' => 'Stone'];
    }

    private function safeColor(?string $color): string
    {
        return array_key_exists((string) $color, $this->colors()) ? $color : '#047857';
    }
}
