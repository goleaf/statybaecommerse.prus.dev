<?php

declare (strict_types=1);
namespace App\Livewire\Pages\Collection;

use App\Models\Collection as CollectionModel;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
/**
 * Show
 * 
 * Livewire component for Show with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property string $slug
 * @property CollectionModel|null $collection
 * @property int $page
 * @property array $brandIds
 * @property string|null $sort
 * @property array $selectedValues
 */
#[Layout('layouts.templates.app')]
class Show extends Component
{
    public string $slug;
    public ?CollectionModel $collection = null;
    #[Url]
    public int $page = 1;
    #[Url]
    public array $brandIds = [];
    #[Url]
    public ?string $sort = null;
    #[Url]
    public array $selectedValues = [];
    /**
     * Initialize the Livewire component with parameters.
     * @param CollectionModel $collection
     * @return void
     */
    public function mount(CollectionModel $collection): void
    {
        abort_if(!app_feature_enabled('collection'), 404);
        // Ensure collection is enabled
        if (!$collection->is_enabled) {
            abort(404);
        }
        $this->collection = $collection;
        $this->slug = $collection->slug;
        // Handle translation redirects if needed
        $locale = app()->getLocale();
        $canonical = $this->collection->translations()->where('locale', $locale)->value('slug') ?: $this->collection->slug;
        if ($canonical && $canonical !== $this->slug) {
            redirect()->to(route('collections.show', ['locale' => $locale, 'slug' => $canonical]), 301)->send();
            exit;
        }
    }
    /**
     * Handle products functionality with proper error handling.
     * @return LengthAwarePaginator
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $collection = $this->collection;
        $query = Product::query()->select(['id', 'slug', 'name', 'summary', 'brand_id', 'published_at'])->with(['brand:id,slug,name', 'media', 'prices' => function ($pq) {
            $pq->whereRelation('currency', 'code', current_currency());
        }, 'prices.currency:id,code'])->withCount('variants');
        $selectedBrandIds = collect($this->brandIds)->map(fn($v) => (int) $v)->filter()->values();
        $selected = collect($this->selectedValues)->map(fn($v) => (int) $v)->filter()->values();
        if ($selected->isNotEmpty()) {
            $query->whereHas('variants.values', function ($q) use ($selected) {
                $q->whereIn('id', $selected->all());
            });
        }
        if ($selectedBrandIds->isNotEmpty()) {
            $query->whereIn('brand_id', $selectedBrandIds->all());
        }
        if ($collection?->isAuto()) {
            // Apply automatic rules defined in Shopper collection rules
            $rules = $collection->rules()->get();
            $matchAll = ($collection->match_conditions->value ?? $collection->match_conditions ?? 'all') === 'all';
            $query->where(function ($outer) use ($rules, $matchAll) {
                // Group rules by type
                $byType = $rules->groupBy(fn($r) => $r->rule->value ?? (string) $r->rule);
                $applyRule = function ($q, $r) {
                    $op = $r->operator->value ?? (string) $r->operator;
                    $val = $r->value;
                    switch ($r->rule->value ?? (string) $r->rule) {
                        case 'product_title':
                            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $val) . '%';
                            return $q->where(function ($w) use ($op, $like) {
                                if (in_array($op, ['contains', 'starts_with', 'ends_with'])) {
                                    $w->where('name', 'like', $like);
                                } elseif ($op === 'not_contains') {
                                    $w->where('name', 'not like', $like);
                                } elseif ($op === 'equals_to') {
                                    $w->where('name', '=', $like);
                                } elseif ($op === 'not_equals_to') {
                                    $w->where('name', '!=', $like);
                                }
                            });
                        case 'product_price':
                            return $q->whereHas('prices', function ($pq) use ($op, $val) {
                                $pq->whereRelation('currency', 'code', current_currency());
                                $pq->when(in_array($op, ['less_than', 'greater_than', 'equals_to', 'not_equals_to']), function ($w) use ($op, $val) {
                                    return match ($op) {
                                        'less_than' => $w->where('amount', '<', (float) $val),
                                        'greater_than' => $w->where('amount', '>', (float) $val),
                                        'equals_to' => $w->where('amount', '=', (float) $val),
                                        'not_equals_to' => $w->where('amount', '!=', (float) $val),
                                    };
                                });
                            });
                        case 'product_brand':
                            return $q->when(is_numeric($val), fn($bq) => $bq->where('brand_id', (int) $val));
                        case 'product_category':
                            return $q->whereHas('categories', fn($cq) => $cq->where('id', (int) $val));
                        default:
                            return $q;
                    }
                };
                if ($matchAll) {
                    foreach ($rules as $r) {
                        $outer->where(fn($w) => $applyRule($w, $r));
                    }
                } else {
                    $outer->where(function ($any) use ($rules, $applyRule) {
                        foreach ($rules as $r) {
                            $any->orWhere(fn($w) => $applyRule($w, $r));
                        }
                    });
                }
            });
        } else {
            $query->whereHas('collections', fn($cq) => $cq->where('collections.id', $collection?->id));
        }
        $query->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now());
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
    /**
     * Handle getAvailableOptionsProperty functionality with proper error handling.
     * @return Illuminate\Support\Collection
     */
    public function getAvailableOptionsProperty(): \Illuminate\Support\Collection
    {
        $collection = $this->collection;
        $builder = Product::query()->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now());
        if ($collection?->isAuto()) {
            $rules = $collection->rules()->get();
            $matchAll = ($collection->match_conditions->value ?? $collection->match_conditions ?? 'all') === 'all';
            $builder->where(function ($outer) use ($rules, $matchAll) {
                $applyRule = function ($q, $r) {
                    $op = $r->operator->value ?? (string) $r->operator;
                    $val = $r->value;
                    switch ($r->rule->value ?? (string) $r->rule) {
                        case 'product_title':
                            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $val) . '%';
                            return $q->where(function ($w) use ($op, $like) {
                                if (in_array($op, ['contains', 'starts_with', 'ends_with'])) {
                                    $w->where('name', 'like', $like);
                                } elseif ($op === 'not_contains') {
                                    $w->where('name', 'not like', $like);
                                } elseif ($op === 'equals_to') {
                                    $w->where('name', '=', $like);
                                } elseif ($op === 'not_equals_to') {
                                    $w->where('name', '!=', $like);
                                }
                            });
                        case 'product_price':
                            return $q->whereHas('prices', function ($pq) use ($op, $val) {
                                $pq->whereRelation('currency', 'code', current_currency());
                                $pq->when(in_array($op, ['less_than', 'greater_than', 'equals_to', 'not_equals_to']), function ($w) use ($op, $val) {
                                    return match ($op) {
                                        'less_than' => $w->where('amount', '<', (float) $val),
                                        'greater_than' => $w->where('amount', '>', (float) $val),
                                        'equals_to' => $w->where('amount', '=', (float) $val),
                                        'not_equals_to' => $w->where('amount', '!=', (float) $val),
                                    };
                                });
                            });
                        case 'product_brand':
                            return $q->when(is_numeric($val), fn($bq) => $bq->where('brand_id', (int) $val));
                        case 'product_category':
                            return $q->whereHas('categories', fn($cq) => $cq->where('id', (int) $val));
                        default:
                            return $q;
                    }
                };
                if ($matchAll) {
                    foreach ($rules as $r) {
                        $outer->where(fn($w) => $applyRule($w, $r));
                    }
                } else {
                    $outer->where(function ($any) use ($rules, $applyRule) {
                        foreach ($rules as $r) {
                            $any->orWhere(fn($w) => $applyRule($w, $r));
                        }
                    });
                }
            });
        } else {
            $builder->whereHas('collections', fn($cq) => $cq->where('collections.id', $collection?->id));
        }
        $products = $builder->with(['variants.values.attribute'])->limit(100)->get();
        return $products->pluck('variants')->flatten()->pluck('values')->flatten()->unique('id')->groupBy('attribute_id')->map(fn($values) => ['attribute' => $values->first()->attribute, 'values' => $values->sortBy('position')->values()]);
    }
    /**
     * Handle clearAttributeFilters functionality with proper error handling.
     * @return void
     */
    public function clearAttributeFilters(): void
    {
        $this->selectedValues = [];
        $this->page = 1;
    }
    /**
     * Handle removeAttributeFilter functionality with proper error handling.
     * @param int $valueId
     * @return void
     */
    public function removeAttributeFilter(int $valueId): void
    {
        $this->selectedValues = array_values(array_filter($this->selectedValues, static fn($id) => (int) $id !== $valueId));
        $this->page = 1;
    }
    /**
     * Handle updatedSort functionality with proper error handling.
     * @return void
     */
    public function updatedSort(): void
    {
        $this->page = 1;
    }
    /**
     * Handle getAvailableBrandsProperty functionality with proper error handling.
     * @return Illuminate\Support\Collection
     */
    public function getAvailableBrandsProperty(): \Illuminate\Support\Collection
    {
        $collection = $this->collection;
        $builder = Product::query()->select(['id', 'brand_id'])->with(['brand:id,name'])->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now());
        if ($collection?->isAuto()) {
            // Reuse the product scoping done in getProductsProperty for rules
            $rules = $collection->rules()->get();
            $matchAll = ($collection->match_conditions->value ?? $collection->match_conditions ?? 'all') === 'all';
            $builder->where(function ($outer) use ($rules, $matchAll) {
                $byType = $rules->groupBy(fn($r) => $r->rule->value ?? (string) $r->rule);
                $applyRule = function ($q, $r) {
                    $op = $r->operator->value ?? (string) $r->operator;
                    $val = $r->value;
                    switch ($r->rule->value ?? (string) $r->rule) {
                        case 'product_title':
                            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $val) . '%';
                            return $q->where(function ($w) use ($op, $like) {
                                if (in_array($op, ['contains', 'starts_with', 'ends_with'])) {
                                    $w->where('name', 'like', $like);
                                } elseif ($op === 'not_contains') {
                                    $w->where('name', 'not like', $like);
                                } elseif ($op === 'equals_to') {
                                    $w->where('name', '=', $like);
                                } elseif ($op === 'not_equals_to') {
                                    $w->where('name', '!=', $like);
                                }
                            });
                        case 'product_price':
                            return $q->whereHas('prices', function ($pq) use ($op, $val) {
                                $pq->whereRelation('currency', 'code', current_currency());
                                $pq->when(in_array($op, ['less_than', 'greater_than', 'equals_to', 'not_equals_to']), function ($w) use ($op, $val) {
                                    return match ($op) {
                                        'less_than' => $w->where('amount', '<', (float) $val),
                                        'greater_than' => $w->where('amount', '>', (float) $val),
                                        'equals_to' => $w->where('amount', '=', (float) $val),
                                        'not_equals_to' => $w->where('amount', '!=', (float) $val),
                                    };
                                });
                            });
                        case 'product_brand':
                            return $q->when(is_numeric($val), fn($bq) => $bq->where('brand_id', (int) $val));
                        case 'product_category':
                            return $q->whereHas('categories', fn($cq) => $cq->where('id', (int) $val));
                        default:
                            return $q;
                    }
                };
                if ($matchAll) {
                    foreach ($rules as $r) {
                        $outer->where(fn($w) => $applyRule($w, $r));
                    }
                } else {
                    $outer->where(function ($any) use ($rules, $applyRule) {
                        foreach ($rules as $r) {
                            $any->orWhere(fn($w) => $applyRule($w, $r));
                        }
                    });
                }
            });
        } else {
            $builder->whereHas('collections', fn($cq) => $cq->where('collections.id', $collection?->id));
        }
        return $builder->whereNotNull('brand_id')->with('brand')->get()->pluck('brand')->filter()->unique('id')->sortBy('name')->values();
    }
    /**
     * Handle clearBrandFilters functionality with proper error handling.
     * @return void
     */
    public function clearBrandFilters(): void
    {
        $this->brandIds = [];
        $this->page = 1;
    }
    /**
     * Handle removeBrandFilter functionality with proper error handling.
     * @param int $brandId
     * @return void
     */
    public function removeBrandFilter(int $brandId): void
    {
        $this->brandIds = array_values(array_filter($this->brandIds, static fn($id) => (int) $id !== $brandId));
        $this->page = 1;
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.pages.collection.show', ['collection' => $this->collection, 'products' => $this->products, 'options' => $this->availableOptions])->title($this->collection?->name ?? __('Collection'));
    }
}