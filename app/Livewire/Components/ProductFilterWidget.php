<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

final class ProductFilterWidget extends Component
{
    public ?int $categoryId = null;

    #[Url]
    public string $search = '';

    #[Url]
    public array $categories = [];

    #[Url]
    public array $brands = [];

    #[Url]
    public array $selectedAttributes = [];

    #[Url]
    public float $minPrice = 0;

    #[Url]
    public float $maxPrice = 10000;

    #[Url]
    public bool $inStock = false;

    #[Url]
    public bool $onSale = false;

    #[Url]
    public string $sortBy = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    public function mount(?int $categoryId = null): void
    {
        $this->categoryId = $categoryId;
        $this->minPrice = (float) $this->minPrice;
        $this->maxPrice = (float) $this->maxPrice;
        $this->updatePriceRange();
        $this->dispatchFilters();
    }

    public function updatedSearch(): void
    {
        $this->dispatchFilters();
    }

    public function updatedCategories(): void
    {
        $this->dispatchFilters();
    }

    public function updatedBrands(): void
    {
        $this->dispatchFilters();
    }

    public function updatedSelectedAttributes(): void
    {
        $this->dispatchFilters();
    }

    public function updatedMinPrice(): void
    {
        $this->minPrice = (float) $this->minPrice;
        $this->dispatchFilters();
    }

    public function updatedMaxPrice(): void
    {
        $this->maxPrice = (float) $this->maxPrice;
        $this->dispatchFilters();
    }

    public function updatedInStock(): void
    {
        $this->dispatchFilters();
    }

    public function updatedOnSale(): void
    {
        $this->dispatchFilters();
    }

    public function updatedSortBy(): void
    {
        $this->dispatchFilters();
    }

    public function updatedSortDirection(): void
    {
        $this->dispatchFilters();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'categories',
            'brands',
            'selectedAttributes',
            'inStock',
            'onSale',
            'sortBy',
            'sortDirection',
        ]);

        $this->updatePriceRange();
        $this->dispatchFilters();
    }

    public function updatePriceRange(): void
    {
        $priceRange = $this->baseProductsQuery()
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        $minPrice = (float) ($priceRange->min_price ?? 0);
        $maxPrice = (float) ($priceRange->max_price ?? 10000);

        $this->minPrice = max(0, $minPrice);
        $this->maxPrice = $maxPrice > 0 ? $maxPrice : 10000;
    }

    #[Computed]
    public function availableCategories(): Collection
    {
        return Category::query()
            ->where('is_visible', true)
            ->with(['translations' => fn ($q) => $q->where('locale', app()->getLocale())])
            ->whereHas('products', function (Builder $productQuery): void {
                $this->applyCategoryScopeToProducts($productQuery);
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function availableBrands(): Collection
    {
        return Brand::query()
            ->where('is_visible', true)
            ->with(['translations' => fn ($q) => $q->where('locale', app()->getLocale())])
            ->whereHas('products', function (Builder $productQuery): void {
                $this->applyCategoryScopeToProducts($productQuery);
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function availableAttributes(): Collection
    {
        return Attribute::query()
            ->where('is_filterable', true)
            ->with(['translations' => fn ($q) => $q->where('locale', app()->getLocale())])
            ->whereHas('values', function (Builder $valueQuery): void {
                $valueQuery
                    ->whereHas('variants')
                    ->whereHas('products', function (Builder $productQuery): void {
                        $this->applyCategoryScopeToProducts($productQuery);
                    });
            })
            ->with(['values' => function ($query): void {
                $query
                    ->with(['translations' => fn ($q) => $q->where('locale', app()->getLocale())])
                    ->whereHas('variants')
                    ->whereHas('products', function (Builder $productQuery): void {
                        $this->applyCategoryScopeToProducts($productQuery);
                    })
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (Attribute $attribute): bool => $attribute->values->isNotEmpty() && ! $this->isIpRatingAttribute($attribute))
            ->values();
    }

    public function getFilteredProductsQuery(): Builder
    {
        $query = $this->baseProductsQuery()->with(['brand', 'categories', 'media', 'translations']);

        if ($this->search !== '') {
            $query->where(function (Builder $searchQuery): void {
                $searchQuery
                    ->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%')
                    ->orWhereHas('brand', function (Builder $brandQuery): void {
                        $brandQuery->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->categories !== []) {
            $query->whereHas('categories', function (Builder $categoryQuery): void {
                $categoryQuery->whereIn('categories.id', $this->normalizeIdList($this->categories));
            });
        }

        if ($this->brands !== []) {
            $query->whereIn('brand_id', $this->normalizeIdList($this->brands));
        }

        if ($this->minPrice > 0 || $this->maxPrice < 10000) {
            $query->whereBetween('price', [$this->minPrice, $this->maxPrice]);
        }

        if ($this->inStock) {
            $query->where('stock_quantity', '>', 0);
        }

        if ($this->onSale) {
            $query->whereHas('discounts', static function (Builder $discountQuery): void {
                $discountQuery->active();
            });
        }

        if ($this->selectedAttributes !== []) {
            foreach ($this->normalizeSelectedAttributes($this->selectedAttributes) as $attributeId => $valueIds) {
                $query->whereHas('attributes', function ($attributeQuery) use ($attributeId, $valueIds): void {
                    $attributeQuery
                        ->where('attributes.id', $attributeId)
                        ->wherePivotIn('attribute_value_id', $valueIds);
                });
            }
        }

        return $query->orderBy($this->sortBy, $this->sortDirection);
    }

    public function render(): View
    {
        return view('livewire.components.product-filter', [
            'availableCategories' => $this->availableCategories,
            'availableBrands'     => $this->availableBrands,
            'availableAttributes' => $this->availableAttributes,
        ]);
    }

    private function dispatchFilters(): void
    {
        $this->dispatch('filter-updated', filters: [
            'search'             => $this->search,
            'categories'         => $this->normalizeIdList($this->categories),
            'brands'             => $this->normalizeIdList($this->brands),
            'selectedAttributes' => $this->normalizeSelectedAttributes($this->selectedAttributes),
            'minPrice'           => $this->minPrice,
            'maxPrice'           => $this->maxPrice,
            'inStock'            => $this->inStock,
            'onSale'             => $this->onSale,
            'sortBy'             => $this->sortBy,
            'sortDirection'      => $this->sortDirection,
        ]);
    }

    private function baseProductsQuery(): Builder
    {
        $query = Product::query()->published();
        $this->applyCategoryScopeToProducts($query);

        return $query;
    }

    private function applyCategoryScopeToProducts(Builder $query): void
    {
        $query->published();

        if ($this->categoryId !== null && $this->categoryId > 0) {
            $query->whereHas('categories', function (Builder $categoryQuery): void {
                $categoryQuery->where('categories.id', $this->categoryId);
            });
        }
    }

    private function isIpRatingAttribute(Attribute $attribute): bool
    {
        $name = mb_strtolower((string) ($attribute->name ?? ''));
        $normalizedName = preg_replace('/\s+/', ' ', trim($name)) ?? $name;
        $slug = mb_strtolower((string) ($attribute->slug ?? ''));

        if (
            $name === 'ip rating'
            || $slug === 'ip-rating'
            || $slug === 'ip_rating'
            || str_contains($slug, 'ip-rating')
            || str_contains($slug, 'ip_rating')
            || (
                str_contains($normalizedName, 'ip')
                && (
                    str_contains($normalizedName, 'rating')
                    || str_contains($normalizedName, 'reiting')
                    || str_contains($normalizedName, 'class')
                    || str_contains($normalizedName, 'klase')
                )
            )
        ) {
            return true;
        }

        foreach ($attribute->translations as $translation) {
            $translatedName = mb_strtolower((string) ($translation->name ?? ''));
            $normalizedTranslatedName = preg_replace('/\s+/', ' ', trim($translatedName)) ?? $translatedName;

            if (
                $translatedName === 'ip rating'
                || (
                    str_contains($normalizedTranslatedName, 'ip')
                    && (
                        str_contains($normalizedTranslatedName, 'rating')
                        || str_contains($normalizedTranslatedName, 'reiting')
                        || str_contains($normalizedTranslatedName, 'class')
                        || str_contains($normalizedTranslatedName, 'klase')
                    )
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, int|string> $values
     * @return array<int, int>
     */
    private function normalizeIdList(array $values): array
    {
        $normalized = array_map(static fn ($value): int => (int) $value, $values);
        $normalized = array_values(array_filter($normalized, static fn (int $value): bool => $value > 0));

        return array_values(array_unique($normalized));
    }

    /**
     * @param  array<int|string, array<int, int|string>> $selectedAttributes
     * @return array<int, array<int, int>>
     */
    private function normalizeSelectedAttributes(array $selectedAttributes): array
    {
        $normalized = [];

        foreach ($selectedAttributes as $attributeId => $valueIds) {
            if (! is_array($valueIds)) {
                continue;
            }

            $normalizedAttributeId = (int) $attributeId;

            if ($normalizedAttributeId <= 0) {
                continue;
            }

            $normalizedValueIds = $this->normalizeIdList($valueIds);

            if ($normalizedValueIds === []) {
                continue;
            }

            $normalized[$normalizedAttributeId] = $normalizedValueIds;
        }

        return $normalized;
    }
}
