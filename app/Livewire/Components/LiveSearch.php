<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class LiveSearch extends Component
{
    #[Validate('nullable|string|max:255')]
    public string $query = '';

    public array $results = [];
    public bool $showResults = false;
    public int $maxResults = 8;

    public function updatedQuery(): void
    {
        if (strlen($this->query) >= 2) {
            $this->performSearch();
            $this->showResults = true;
        } else {
            $this->results = [];
            $this->showResults = false;
        }
    }

    public function performSearch(): void
    {
        $products = Product::query()
            ->with(['media', 'brand'])
            ->where('is_visible', true)
            ->where(function ($q) {
                $q
                    ->where('name', 'like', '%' . $this->query . '%')
                    ->orWhere('description', 'like', '%' . $this->query . '%')
                    ->orWhere('sku', 'like', '%' . $this->query . '%');
            })
            ->limit($this->maxResults)
            ->get()
            ->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'type' => 'product',
                    'title' => $product->name,
                    'subtitle' => $product->brand?->name,
                    'description' => $product->sku,
                    'price' => $product->price,
                    'image' => $product->getFirstMediaUrl('images', 'thumb'),
                    'url' => route('product.show', $product->slug),
                ];
            });

        $categories = Category::query()
            ->where('is_visible', true)
            ->where('name', 'like', '%' . $this->query . '%')
            ->limit(3)
            ->get()
            ->map(function (Category $category) {
                return [
                    'id' => $category->id,
                    'type' => 'category',
                    'title' => $category->name,
                    'subtitle' => __('ecommerce.category'),
                    'description' => $category->products_count . ' ' . __('ecommerce.products'),
                    'url' => route('category.show', ['category' => $category->slug]),
                ];
            });

        $brands = Brand::query()
            ->where('is_enabled', true)
            ->where('name', 'like', '%' . $this->query . '%')
            ->limit(3)
            ->get()
            ->map(function (Brand $brand) {
                return [
                    'id' => $brand->id,
                    'type' => 'brand',
                    'title' => $brand->name,
                    'subtitle' => __('ecommerce.brand'),
                    'description' => $brand->products_count . ' ' . __('ecommerce.products'),
                    'url' => route('brands.show', $brand->slug),
                ];
            });

        $this->results = $products
            ->concat($categories)
            ->concat($brands)
            ->take($this->maxResults)
            ->toArray();
    }

    public function selectResult(array $result): void
    {
        $this->redirect($result['url']);
    }

    public function clearSearch(): void
    {
        $this->query = '';
        $this->results = [];
        $this->showResults = false;
    }

    public function hideResults(): void
    {
        $this->showResults = false;
    }

    public function render(): View
    {
        return view('livewire.components.live-search');
    }
}

