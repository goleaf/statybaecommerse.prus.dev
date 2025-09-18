<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Category;
use App\Models\Product;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

final class ProductCatalogue extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    use WithCart;
    use WithNotifications;
    use WithPagination;

    public int $perPage = 16;

    public string $sort = 'latest';

    public ?int $category = null;

    public string $search = '';

    protected $queryString = [
        'sort' => ['except' => 'latest'],
        'category' => ['except' => null],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories(): array
    {
        return Category::query()
            ->where('is_visible', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['brand', 'categories', 'media'])
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($this->category) {
            $query->whereHas('categories', fn ($relation) => $relation->where('categories.id', $this->category));
        }

        if (filled($this->search)) {
            $query->where(function ($builder): void {
                $builder
                    ->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        $query = match ($this->sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popular' => $query->withCount('reviews')->orderByDesc('reviews_count')->orderByDesc('published_at'),
            default => $query->orderByDesc('published_at'),
        };

        return $query->paginate($this->perPage);
    }

    public function productCatalogueSchema(Schema $schema): Schema
    {
        return $schema->components([
            ViewEntry::make('catalogue')
                ->label('')
                ->view('livewire.home.product-catalogue')
                ->viewData(fn (): array => [
                    'products' => $this->products(),
                    'categories' => $this->categories(),
                    'sort' => $this->sort,
                    'search' => $this->search,
                    'selectedCategory' => $this->category,
                ]),
        ]);
    }

    public function render(): View
    {
        return view('livewire.home.catalogue-wrapper');
    }
}
