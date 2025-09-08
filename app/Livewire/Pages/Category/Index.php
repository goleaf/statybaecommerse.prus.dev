<?php declare(strict_types=1);

namespace App\Livewire\Pages\Category;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

final class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $brandId = null;
    public ?float $priceMin = null;
    public ?float $priceMax = null;
    public bool $hasProducts = false;
    public string $sort = 'name_asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'brandId' => ['except' => null],
        'priceMin' => ['except' => null],
        'priceMax' => ['except' => null],
        'hasProducts' => ['except' => false],
        'sort' => ['except' => 'name_asc'],
    ];

    public function updating($field): void
    {
        if (in_array($field, ['search', 'brandId', 'priceMin', 'priceMax', 'hasProducts', 'sort'])) {
            $this->resetPage();
        }
    }

    public function getBrandsProperty()
    {
        return Brand::query()
            ->where('is_enabled', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getCategoriesProperty(): LengthAwarePaginator
    {
        $query = Category::query()
            ->with(['media'])
            ->withCount(['products' => function ($q) {
                $q->where('is_visible', true);
                if ($this->brandId) {
                    $q->where('brand_id', $this->brandId);
                }
                if ($this->priceMin !== null) {
                    $q->where('price', '>=', (float) $this->priceMin);
                }
                if ($this->priceMax !== null) {
                    $q->where('price', '<=', (float) $this->priceMax);
                }
            }])
            ->where('is_visible', true);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->hasProducts) {
            $query->has('products');
        }

        $query->when($this->sort === 'name_asc', fn($q) => $q->orderBy('name'))
              ->when($this->sort === 'name_desc', fn($q) => $q->orderByDesc('name'))
              ->when($this->sort === 'products_desc', fn($q) => $q->orderByDesc('products_count'))
              ->when($this->sort === 'products_asc', fn($q) => $q->orderBy('products_count'))
              ->when(!in_array($this->sort, ['name_asc','name_desc','products_desc','products_asc']), fn($q) => $q->orderBy('name'));

        return $query->paginate(12);
    }

    public function render()
    {
        return view('livewire.pages.category.index', [
            'categories' => $this->categories,
            'brands' => $this->brands,
        ])->layout('components.layouts.base', [
            'title' => __('Categories')
        ]);
    }
}
