<?php

namespace App\Livewire\Categories;

use App\Actions\Categories\UpdateCategory;
use App\Data\Category\UpdateCategoryData;
use App\Enums\CategoryType;
use App\Models\Category;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public Category $category;

    public string $name;

    public string $type;

    public string $color;

    public string $icon;

    public ?string $domainError = null;

    public function mount(Category $category): void
    {
        Gate::authorize('update', $category);
        $this->category = $category;
        $this->name = $category->name;
        $this->type = $category->type->value;
        $this->color = array_key_exists((string) $category->color, $this->colors()) ? $category->color : '#57534E';
        $this->icon = array_key_exists((string) $category->icon, $this->icons()) ? $category->icon : 'wallet';
    }

    public function save(UpdateCategory $action): mixed
    {
        Gate::authorize('update', $this->category);
        $validated = $this->validate($this->rules());

        try {
            $action->handle($this->category, new UpdateCategoryData($validated['name'], CategoryType::from($validated['type']), $validated['icon'], $validated['color']));
        } catch (DomainException $exception) {
            $this->domainError = $exception->getMessage();

            return null;
        }

        session()->flash('success', 'Category updated successfully.');

        return $this->redirectRoute('categories', ['type' => $validated['type']], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.categories.edit', ['types' => CategoryType::options(), 'colors' => $this->colors(), 'icons' => $this->icons()])
            ->layout('layouts.app', ['title' => 'Edit Category | Spendly', 'header' => 'Categories']);
    }

    private function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255', Rule::unique('categories')->where(fn ($query) => $query->where('user_id', auth()->id())->where('type', $this->type))->ignore($this->category)], 'type' => ['required', Rule::enum(CategoryType::class)], 'color' => ['required', Rule::in(array_keys($this->colors()))], 'icon' => ['required', Rule::in(array_keys($this->icons()))]];
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
