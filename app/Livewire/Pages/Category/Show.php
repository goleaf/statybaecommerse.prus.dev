<?php declare(strict_types=1);

namespace App\Livewire\Pages\Category;

use App\Models\Category as CategoryModel;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.templates.app')]
class Show extends Component
{
    public string $slug;

    public ?CategoryModel $category = null;

    #[Url]
    public int $page = 1;

    #[Url]
    public array $selectedValues = [];

    #[Url]
    public ?string $sort = null;

    public function clearFilters(): void
    {
        $this->selectedValues = [];
        $this->page = 1;
    }

    public function removeFilter(int $valueId): void
    {
        $this->selectedValues = array_values(array_filter(
            $this->selectedValues,
            static fn($id) => (int) $id !== $valueId
        ));
        $this->page = 1;
    }

    public function updatedSort(): void
    {
        $this->page = 1;
    }

    public function mount(string $slug): void
    {
        abort_if(!shopper_feature_enabled('category'), 404);
        $this->slug = $slug;
        $locale = app()->getLocale();
        $this->category = CategoryModel::query()
            ->where('is_enabled', true)
            ->where(function ($q) use ($slug, $locale) {
                $q
                    ->where('slug', $slug)
                    ->orWhereExists(function ($sq) use ($slug, $locale) {
                        $sq
                            ->selectRaw('1')
                            ->from('sh_category_translations as t')
                            ->whereColumn('t.category_id', 'sh_categories.id')
                            ->where('t.locale', $locale)
                            ->where('t.slug', $slug);
                    });
            })
            ->first();

        abort_if(is_null($this->category), 404);

        $canonical = $this->category->translations()->where('locale', $locale)->value('slug') ?: $this->category->slug;
        if ($canonical && $canonical !== $slug) {
            redirect()->to(route('category.show', ['locale' => $locale, 'slug' => $canonical]), 301)->send();
            exit;
        }
    }

    public function getProductsProperty(): LengthAwarePaginator
    {
        $categoryId = $this->category?->id;
        $selected = collect($this->selectedValues)->filter()->values();

        $query = Product::query()
            ->select(['id', 'slug', 'name', 'summary', 'brand_id', 'published_at'])
            ->with([
                'brand:id,slug,name',
                'media',
                'prices' => function ($pq) {
                    $pq->whereRelation('currency', 'code', current_currency());
                },
                'prices.currency:id,code',
            ])
            ->withCount('variants')
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('categories', fn($q) => $q->where('id', $categoryId));

        if ($selected->isNotEmpty()) {
            $query->whereHas('variants.values', function ($q) use ($selected) {
                $q->whereIn('id', $selected);
            });
        }

        // Sorting
        switch ($this->sort) {
            case 'name_asc':
                $query->orderBy('name');
                break;
            case 'name_desc':
                $query->orderByDesc('name');
                break;
            default:
                $query->orderByDesc('published_at');
        }

        return $query->paginate(12);
    }

    public function getAvailableOptionsProperty(): Collection
    {
        $categoryId = $this->category?->id;

        $products = Product::query()
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('categories', fn($q) => $q->where('id', $categoryId))
            ->with(['variants.values.attribute'])
            ->limit(100)
            ->get();

        return $products
            ->pluck('variants')
            ->flatten()
            ->pluck('values')
            ->flatten()
            ->unique('id')
            ->groupBy('attribute_id')
            ->map(fn($values) => [
                'attribute' => $values->first()->attribute,
                'values' => $values->sortBy('position')->values(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.pages.category.show', [
            'category' => $this->category,
            'products' => $this->products,
            'options' => $this->availableOptions,
        ])->title($this->category?->name ?? __('Category'));
    }
}
