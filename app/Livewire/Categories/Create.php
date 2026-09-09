<?php

namespace App\Livewire\Categories;

use App\Actions\Categories\CreateCategory;
use App\Data\Category\CreateCategoryData;
use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $type = 'expense';

    public string $color = '#DC2626';

    public string $icon = 'wallet';

    public function mount(): void
    {
        Gate::authorize('create', Category::class);
        $requestedType = request()->query('type');
        if (is_string($requestedType) && CategoryType::tryFrom($requestedType)) {
            $this->type = $requestedType;
        }
    }

    public function save(CreateCategory $action): mixed
    {
        Gate::authorize('create', Category::class);
        $validated = $this->validate($this->rules());
        $action->handle(new CreateCategoryData(
            userId: auth()->id(), name: $validated['name'], type: CategoryType::from($validated['type']), icon: $validated['icon'], color: $validated['color'],
        ));
        session()->flash('success', 'Category created successfully.');

        return $this->redirectRoute('categories', ['type' => $validated['type']], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.categories.create', ['types' => CategoryType::options(), 'colors' => $this->colors(), 'icons' => $this->icons()])
            ->layout('layouts.app', ['title' => 'Create Category | Spendly', 'header' => 'Categories']);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->where(fn ($query) => $query->where('user_id', auth()->id())->where('type', $this->type))],
            'type' => ['required', Rule::enum(CategoryType::class)],
            'color' => ['required', Rule::in(array_keys($this->colors()))],
            'icon' => ['required', Rule::in(array_keys($this->icons()))],
        ];
    }

    private function colors(): array
    {
        return ['#DC2626' => 'Red', '#D97706' => 'Amber', '#047857' => 'Emerald', '#2563EB' => 'Blue', '#7C3AED' => 'Violet', '#57534E' => 'Stone'];
    }

    private function icons(): array
    {
        return ['wallet' => 'Wallet', 'cart' => 'Shopping', 'car' => 'Transport', 'home' => 'Home', 'briefcase' => 'Work'];
    }
}
