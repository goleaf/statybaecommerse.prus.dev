<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Livewire\Component;

final class SimpleHome extends Component
{
    public function render()
    {
        $stats = [
            'products_count' => Product::where('is_visible', true)->count(),
            'categories_count' => Category::where('is_visible', true)->count(),
            'brands_count' => Brand::where('is_enabled', true)->count(),
            'reviews_count' => Review::where('is_approved', true)->count(),
        ];

        $featuredProducts = Product::query()
            ->with(['brand', 'media'])
            ->where('is_visible', true)
            ->where('is_featured', true)
            ->limit(8)
            ->get();

        return view('livewire.pages.simple-home', [
            'stats' => $stats,
            'featuredProducts' => $featuredProducts,
        ])->layout('components.layouts.base', [
            'title' => __('Home') . ' - ' . config('app.name')
        ]);
    }
}
