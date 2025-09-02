<?php declare(strict_types=1);

namespace App\Livewire\Pages\Category;

use App\Models\Category;
use Livewire\Component;

final class Index extends Component
{
    public function getCategoriesProperty()
    {
        return Category::where('is_visible', true)
            ->with(['media'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.category.index', [
            'categories' => $this->categories,
        ])->layout('components.layouts.base', [
            'title' => __('Categories')
        ]);
    }
}