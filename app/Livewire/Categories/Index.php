<?php

namespace App\Livewire\Categories;

use App\Actions\Categories\DeleteCategory;
use App\Enums\CategoryType;
use App\Models\Category;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(except: 'expense')]
    public string $type = 'expense';

    public ?string $deletingCategory = null;

    public ?string $deletionError = null;

    public function setType(string $type): void
    {
        if (CategoryType::tryFrom($type)) {
            $this->type = $type;
        }
    }

    public function confirmDeletion(string $publicId): void
    {
        $category = $this->ownedCategory($publicId);
        Gate::authorize('delete', $category);
        $this->deletionError = null;
        $this->deletingCategory = $publicId;
    }

    public function delete(DeleteCategory $action): void
    {
        abort_unless($this->deletingCategory, 404);
        $category = $this->ownedCategory($this->deletingCategory);
        Gate::authorize('delete', $category);

        try {
            $action->handle($category);
        } catch (DomainException $exception) {
            $this->deletionError = $exception->getMessage();

            return;
        }

        $this->deletingCategory = null;
        session()->flash('success', 'Category deleted successfully.');
    }

    public function render(): View
    {
        $userId = auth()->id();
        abort_unless($userId, 401);
        $selectedType = CategoryType::tryFrom($this->type) ?? CategoryType::Expense;

        $counts = Category::query()->where('user_id', $userId)->selectRaw('type, COUNT(*) as aggregate')->groupBy('type')->pluck('aggregate', 'type');
        $categories = Category::query()->where('user_id', $userId)->where('type', $selectedType)
            ->withCount(['transactions', 'budgets', 'recurringTransactions'])->orderBy('name')->get();

        return view('livewire.categories.index', [
            'categories' => $categories,
            'counts' => ['expense' => (int) ($counts['expense'] ?? 0), 'income' => (int) ($counts['income'] ?? 0)],
            'selectedType' => $selectedType,
        ])->layout('layouts.app', ['title' => 'Categories | Spendly', 'header' => 'Categories']);
    }

    private function ownedCategory(string $publicId): Category
    {
        return Category::query()->where('user_id', auth()->id())->where('public_id', $publicId)->firstOrFail();
    }
}
