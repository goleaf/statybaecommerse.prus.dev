<?php declare(strict_types=1);

namespace App\Livewire\Pages\Category;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

final class Show extends Component
{
    use WithPagination;

    public Category $category;
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    public function mount(Category $category): void
    {
        // Ensure category is visible and load media
        if (!$category->is_visible) {
            abort(404);
        }
        
        $category->load(['media']);
        $this->category = $category;
    }

    public function getProductsProperty()
    {
        return $this->category->products()
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
            'title' => $this->category->name
        ]);
    }
}