<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Home extends Component
{
    #[Computed]
    public function stats(): array
    {
        return [
            'products_count' => Product::where('is_visible', true)->count(),
            'categories_count' => Category::where('is_visible', true)->count(),
            'brands_count' => Brand::where('is_enabled', true)->count(),
            'reviews_count' => Review::where('is_approved', true)->count(),
            'avg_rating' => Review::where('is_approved', true)->avg('rating') ?? 0,
        ];
    }

    public function render()
    {
        return view('livewire.pages.home', [
            'stats' => $this->stats,
        ])->layout('components.layouts.base', [
            'title' => __('Home').' - '.config('app.name'),
        ]);
    }
}
