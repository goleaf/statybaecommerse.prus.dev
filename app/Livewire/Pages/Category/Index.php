<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Category;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final /**
 * Index
 * 
 * Livewire component for reactive frontend functionality.
 */
class Index extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: [])]
    public array $selectedBrandIds = [];

    #[Url(except: [])]
    public array $selectedCollectionIds = [];

    #[Url(except: [])]
    public array $selectedCategoryIds = [];

    #[Url(except: null)]
    public ?float $priceMin = null;

    #[Url(except: null)]
    public ?float $priceMax = null;

    #[Url(except: false)]
    public bool $inStock = false;

    #[Url(except: false)]
    public bool $onSale = false;

    #[Url(except: false)]
    public bool $hasProducts = false;

    #[Url(except: 'name_asc')]
    public string $sort = 'name_asc';

    #[Url(except: 12)]
    public int $perPage = 12;

    public bool $sidebarOpen = false;

    public function mount(): void
    {
        // Normalize legacy single brand param to array if present via URL
        if (property_exists($this, 'brandId') && $this->brandId) {
            $this->selectedBrandIds = [$this->brandId];
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('search')
                    ->label(__('Search'))
                    ->placeholder(__('Search categories...'))
                    ->live(debounce: 400)
                    ->afterStateUpdated(fn () => $this->resetPage()),
                Select::make('brandId')
                    ->label(__('Brand'))
                    ->placeholder(__('All brands'))
                    ->options($this->getBrandOptions())
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetPage()),
                TextInput::make('priceMin')
                    ->label(__('Min price'))
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn () => $this->resetPage()),
                TextInput::make('priceMax')
                    ->label(__('Max price'))
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn () => $this->resetPage()),
                Checkbox::make('hasProducts')
                    ->label(__('Only categories with products'))
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetPage()),
                Select::make('sort')
                    ->label(__('Sort by'))
                    ->options([
                        'name_asc' => __('Name (A–Z)'),
                        'name_desc' => __('Name (Z–A)'),
                        'products_desc' => __('Most products'),
                        'products_asc' => __('Fewest products'),
                    ])
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetPage()),
            ]);
    }

    #[Computed]
    public function brands()
    {
        return Brand::query()
            ->where('is_enabled', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function getBrandOptions(): array
    {
        return $this->brands->pluck('name', 'id')->filter(fn ($label) => filled($label))->toArray();
    }

    #[Computed]
    public function collections()
    {
        return Collection::query()
            ->visible()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function facetBrands(): array
    {
        $brands = Brand::query()->where('is_enabled', true)->orderBy('name')->get(['id', 'name']);

        $countsByBrand = [];
        foreach ($brands as $brand) {
            $countsByBrand[$brand->id] = $this
                ->baseProductQuery()
                ->where('brand_id', $brand->id)
                ->count();
        }

        return $brands->map(fn ($b) => [
            'id' => (int) $b->id,
            'name' => (string) $b->name,
            'count' => (int) ($countsByBrand[$b->id] ?? 0),
        ])->toArray();
    }

    #[Computed]
    public function facetCollections(): array
    {
        $collections = Collection::query()->visible()->orderBy('name')->get(['id', 'name']);

        $countsByCollection = [];
        foreach ($collections as $col) {
            $countsByCollection[$col->id] = $this
                ->baseProductQuery()
                ->whereHas('collections', fn (Builder $q) => $q->where('collections.id', $col->id))
                ->count();
        }

        return $collections->map(fn ($c) => [
            'id' => (int) $c->id,
            'name' => (string) $c->name,
            'count' => (int) ($countsByCollection[$c->id] ?? 0),
        ])->toArray();
    }

    #[Computed]
    public function facetCategories(): array
    {
        $categories = Category::query()->visible()->orderBy('name')->get(['id', 'name']);

        $counts = [];
        foreach ($categories as $cat) {
            $counts[$cat->id] = $this
                ->baseProductQuery()
                ->whereHas('categories', fn (Builder $q) => $q->where('categories.id', $cat->id))
                ->count();
        }

        return $categories->map(fn ($c) => [
            'id' => (int) $c->id,
            'name' => (string) $c->name,
            'count' => (int) ($counts[$c->id] ?? 0),
        ])->toArray();
    }

    private function baseProductQuery(): Builder
    {
        return Product::query()
            ->visible()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when(! empty($this->selectedBrandIds), fn (Builder $q) => $q->whereIn('brand_id', $this->selectedBrandIds))
            ->when(! empty($this->selectedCollectionIds), fn (Builder $q) => $q->whereHas('collections', fn (Builder $qq) => $qq->whereIn('collections.id', $this->selectedCollectionIds)))
            ->when($this->priceMin !== null, fn (Builder $q) => $q->where('price', '>=', (float) $this->priceMin))
            ->when($this->priceMax !== null, fn (Builder $q) => $q->where('price', '<=', (float) $this->priceMax))
            ->when($this->inStock, fn (Builder $q) => $q->where('stock_quantity', '>', 0))
            ->when($this->onSale, fn (Builder $q) => $q->whereNotNull('sale_price'))
            ->when($this->search !== '', function (Builder $q) {
                $q->where(function (Builder $qq) {
                    $qq
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%');
                });
            });
    }

    #[Computed]
    public function categories(): LengthAwarePaginator
    {
        $query = Category::query()
            ->with(['media'])
            ->withCount(['products' => function (Builder $q) {
                $q
                    ->where('is_visible', true)
                    ->when(! empty($this->selectedBrandIds), fn (Builder $qq) => $qq->whereIn('brand_id', $this->selectedBrandIds))
                    ->when(! empty($this->selectedCollectionIds), fn (Builder $qq) => $qq->whereHas('collections', fn (Builder $c) => $c->whereIn('collections.id', $this->selectedCollectionIds)))
                    ->when($this->priceMin !== null, fn (Builder $qq) => $qq->where('price', '>=', (float) $this->priceMin))
                    ->when($this->priceMax !== null, fn (Builder $qq) => $qq->where('price', '<=', (float) $this->priceMax))
                    ->when($this->inStock, fn (Builder $qq) => $qq->where('stock_quantity', '>', 0))
                    ->when($this->onSale, fn (Builder $qq) => $qq->whereNotNull('sale_price'));
            }])
            ->where('is_visible', true);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->hasProducts) {
            $query->has('products');
        }

        if (! empty($this->selectedCategoryIds)) {
            $query->where(function (Builder $q) {
                $q
                    ->whereIn('id', $this->selectedCategoryIds)
                    ->orWhereIn('parent_id', $this->selectedCategoryIds);
            });
        }

        $query
            ->when($this->sort === 'name_asc', fn ($q) => $q->orderBy('name'))
            ->when($this->sort === 'name_desc', fn ($q) => $q->orderByDesc('name'))
            ->when($this->sort === 'products_desc', fn ($q) => $q->orderByDesc('products_count'))
            ->when($this->sort === 'products_asc', fn ($q) => $q->orderBy('products_count'))
            ->when(! in_array($this->sort, ['name_asc', 'name_desc', 'products_desc', 'products_asc']), fn ($q) => $q->orderBy('name'));

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.pages.category.index')
            ->layout('components.layouts.base', [
                'title' => __('Categories'),
            ]);
    }
}
