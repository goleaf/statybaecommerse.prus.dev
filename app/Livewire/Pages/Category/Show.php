<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Category;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

final /**
 * Show
 * 
 * Livewire component for reactive frontend functionality.
 */
class Show extends Component
{
    use WithPagination;

    public Category $category;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function mount(Category $category): void
    {
        // Ensure category is visible and load media and translations
        if (! $category->is_visible) {
            abort(404);
        }

        // Optimize relationship loading using Laravel 12.10 relationLoaded dot notation
        if (!$category->relationLoaded('media') || !$category->relationLoaded('translations')) {
            $category->load(['media', 'translations']);
        }
        $this->category = $category;
    }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        return $this
            ->category
            ->products()
            ->where('is_visible', true)
            ->with(['brand', 'media'])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.pages.category.show', [
            'products' => $this->products,
        ])->layout('components.layouts.base', [
            'title' => $this->category->name,
        ]);
    }
}
