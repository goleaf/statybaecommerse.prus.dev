<?php declare(strict_types=1);

namespace App\Livewire\Pages\Brand;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.templates.app')]
final class Show extends Component
{
    use WithPagination;

    public Brand $brand;
    public string $sortBy = 'latest';
    public string $priceRange = 'all';

    public function mount(Brand $brand): void
    {
        // Ensure brand is enabled and load relationships
        if (!$brand->is_enabled) {
            abort(404);
        }
        
        $brand->load(['translations' => function ($q) {
            $q->where('locale', app()->getLocale());
        }, 'media']);
        $this->brand = $brand;
    }

    public function getProductsProperty()
    {
        $query = Product::query()
            ->select(['id', 'slug', 'name', 'summary', 'brand_id', 'published_at'])
            ->with([
                'translations' => function ($q) {
                    $q->where('locale', app()->getLocale());
                },
                'brand:id,slug,name',
                'brand.translations' => function ($q) {
                    $q->where('locale', app()->getLocale());
                },
                'media',
                'prices' => function ($pq) {
                    $pq->whereRelation('currency', 'code', current_currency());
                },
                'prices.currency:id,code',
            ])
            ->withCount('variants')
            ->where('brand_id', $this->brand->id)
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        // Apply sorting
        match ($this->sortBy) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            'oldest' => $query->orderBy('published_at'),
            default => $query->orderByDesc('published_at'),
        };

        return $query->paginate(12);
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function updatedPriceRange(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.pages.brand.show', [
            'products' => $this->products,
        ])->title($this->brand->name . ' - ' . __('translations.brands'));
    }
}
