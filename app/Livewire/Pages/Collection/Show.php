<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Collection;

use App\Data\Collection\AttributeFilterGroupData;
use App\Data\Collection\AttributeFilterValueData;
use App\Models\AttributeValue;
use App\Models\Collection as CollectionModel;
use App\Models\CollectionRule;
use App\Models\Product;
use App\Models\ProductVariant;
use BackedEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTags;
use App\Support\Cache\TagAwareCache;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Show
 *
 * Livewire component for Show with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string                 $slug
 * @property CollectionModel|null   $collection
 * @property int                    $page
 * @property array<int, int|string> $brandIds
 * @property string|null            $sort
 * @property array<int, int|string> $selectedValues
 * @property-read LengthAwarePaginator<int, Product> $products
 * @property-read Collection<int, AttributeFilterGroupData> $availableOptions
 * @property-read Collection<int, \App\Models\Brand> $availableBrands
 */
#[Layout('components.layouts.base')]
class Show extends Component
{
    public string $slug;

    public ?CollectionModel $collection = null;

    #[Url]
    public int $page = 1;

    /**
     * @var array<int, int|string>
     */
    #[Url]
    public array $brandIds = [];

    #[Url]
    public ?string $sort = null;

    /**
     * @var array<int, int|string>
     */
    #[Url]
    public array $selectedValues = [];

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(CollectionModel $collection): void
    {
        abort_if(! app_feature_enabled('collection'), 404);

        if (! $collection->is_enabled) {
            abort(404);
        }

        $this->collection = $collection;
        $this->slug = $collection->slug;

        $locale = app()->getLocale();
        $canonical = $this->collection->translations()->where('locale', $locale)->value('slug') ?: $this->collection->slug;

        if ($canonical && $canonical !== $this->slug) {
            redirect()->to(route('localized.collections.show', ['locale' => $locale, 'collection' => $canonical]), 301)->send();
            exit;
        }
    }

    /**
     * Resolve the paginated product list for the current collection context.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $collection = $this->collection;

        $query = Product::query()
            ->select(['id', 'slug', 'name', 'summary', 'brand_id', 'published_at'])
            ->withCount('variants');

        $query->with(['brand:id,slug,name', 'media', 'prices.currency:id,code']);

        $query->with(['prices' => static function (Builder $priceQuery): void {
            // Only eager load prices for the active currency to avoid leaking unused rows.
            $priceQuery->whereRelation('currency', 'code', current_currency());
        }]);

        $selectedBrandIds = collect($this->brandIds)
            ->map(static fn (mixed $value): int => (int) $value)
            ->filter()
            ->values();

        $selectedAttributes = collect($this->selectedValues)
            ->map(static fn (mixed $value): int => (int) $value)
            ->filter()
            ->values();

        if ($selectedAttributes->isNotEmpty()) {
            $query->whereHas('variants.values', static function (Builder $valueQuery) use ($selectedAttributes): void {
                // Limit products to those that expose the selected attribute values.
                $valueQuery->whereIn('id', $selectedAttributes->all());
            });
        }

        if ($selectedBrandIds->isNotEmpty()) {
            $query->whereIn('brand_id', $selectedBrandIds->all());
        }

        $this->applyCollectionScope($query, $collection);

        $query
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        match ($this->sort) {
            'name_asc'  => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            default     => $query->orderByDesc('published_at'),
        };

        return $query->paginate(12);
    }

    /**
     * Provide the available attribute filters for the current collection.
     *
     * @return Collection<int, AttributeFilterGroupData>
     */
    #[Computed]
    public function getAvailableOptionsProperty(): Collection
    {
        $collection = $this->collection;

        // Pull the cached attribute/value matrix and then decorate it with the
        // current selection state so Livewire hydration remains stable.
        $baseGroups = $this->resolveCachedFilterGroups($collection);

        $selectedValues = collect($this->selectedValues)
            ->map(static fn (mixed $value): int => (int) $value)
            ->filter()
            ->values();

        return $baseGroups->map(
            function (AttributeFilterGroupData $group) use ($selectedValues): AttributeFilterGroupData {
                // Rebuild the value list with updated selection markers for the UI.
                $values = $group->values->map(
                    static function (AttributeFilterValueData $value) use ($selectedValues): AttributeFilterValueData {
                        $isSelected = $selectedValues->contains($value->id);

                        return new AttributeFilterValueData($value->id, $value->label, $isSelected);
                    }
                );

                return new AttributeFilterGroupData($group->attributeId, $group->attributeName, $values);
            }
        );
    }

    /**
     * Resolve and cache the attribute filter matrix for the current collection.
     */
    private function resolveCachedFilterGroups(?CollectionModel $collection): Collection
    {
        $locale = app()->getLocale();

        /** @var Collection<int, AttributeFilterGroupData> $groups */
        $groups = TagAwareCache::remember(
            CacheKeys::collectionFilters($collection?->getKey() ?? 0, $locale),
            now()->addMinutes(30),
            function () use ($collection): Collection {
                $builder = Product::query()
                    ->where('is_visible', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());

                $this->applyCollectionScope($builder, $collection);

                /** @var EloquentCollection<int, Product> $products */
                $products = $builder->with(['variants.values.attribute'])->get();

                $groups = [];

                foreach ($products as $product) {
                    /** @var EloquentCollection<int, ProductVariant> $variants */
                    $variants = $product->variants;

                    foreach ($variants as $variant) {
                        /** @var EloquentCollection<int, AttributeValue> $values */
                        $values = $variant->values;

                        foreach ($values as $value) {
                            $attribute = $value->attribute;

                            if ($attribute === null) {
                                continue;
                            }

                            $attributeId = (int) $value->attribute_id;

                            if (! array_key_exists($attributeId, $groups)) {
                                // Initialise a new group the first time an attribute is discovered.
                                $groups[$attributeId] = new AttributeFilterGroupData(
                                    attributeId: $attributeId,
                                    attributeName: (string) $attribute->name,
                                    values: collect()
                                );
                            }

                            /** @var AttributeFilterGroupData $group */
                            $group = $groups[$attributeId];

                            $groups[$attributeId] = new AttributeFilterGroupData(
                                attributeId: $group->attributeId,
                                attributeName: $group->attributeName,
                                values: $group->values->put(
                                    (int) $value->id,
                                    new AttributeFilterValueData(
                                        id: (int) $value->id,
                                        label: (string) $value->name,
                                        selected: false,
                                    )
                                )
                            );
                        }
                    }
                }

                return collect($groups)
                    ->map(static function (AttributeFilterGroupData $group): AttributeFilterGroupData {
                        // Ensure the nested collection is sequential for Blade iteration.
                        return new AttributeFilterGroupData(
                            attributeId: $group->attributeId,
                            attributeName: $group->attributeName,
                            values: $group->values->values(),
                        );
                    })
                    ->values();
            },
            [
                CacheTags::collections(),
                CacheTags::products(),
                CacheTags::locale($locale),
            ]
        );

        return $groups;
    }

    /**
     * Resolve the list of brands that are available to be filtered within the collection.
     *
     * @return Collection<int, \App\Models\Brand>
     */
    #[Computed]
    public function getAvailableBrandsProperty(): Collection
    {
        $collection = $this->collection;

        $builder = Product::query()
            ->select(['id', 'brand_id'])
            ->with(['brand:id,name'])
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        $this->applyCollectionScope($builder, $collection);

        return $builder
            ->whereNotNull('brand_id')
            ->with('brand')
            ->get()
            ->pluck('brand')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    /**
     * Clear all attribute filters at once.
     */
    public function clearAttributeFilters(): void
    {
        $this->selectedValues = [];
        $this->page = 1;
    }

    /**
     * Remove a specific attribute value from the filter set.
     */
    public function removeAttributeFilter(int $valueId): void
    {
        $this->selectedValues = array_values(array_filter(
            $this->selectedValues,
            static fn (int|string $id): bool => (int) $id !== $valueId
        ));
        $this->page = 1;
    }

    /**
     * Reset pagination whenever the sort order changes.
     */
    public function updatedSort(): void
    {
        $this->page = 1;
    }

    /**
     * Clear all active brand filters.
     */
    public function clearBrandFilters(): void
    {
        $this->brandIds = [];
        $this->page = 1;
    }

    /**
     * Remove a single brand filter.
     */
    public function removeBrandFilter(int $brandId): void
    {
        $this->brandIds = array_values(array_filter(
            $this->brandIds,
            static fn (int|string $id): bool => (int) $id !== $brandId
        ));
        $this->page = 1;
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.pages.collection.show', [
            'collection' => $this->collection,
            'products'   => $this->products,
            'options'    => $this->availableOptions,
        ])->title($this->collection?->name ?? __('Collection'));
    }

    /**
     * Apply the correct collection scoping behaviour depending on whether the collection is automatic or manual.
     */
    private function applyCollectionScope(Builder $query, ?CollectionModel $collection): void
    {
        if ($collection === null) {
            return;
        }

        if (! $collection->isAuto()) {
            $query->whereHas('collections', static function (Builder $relation) use ($collection): void {
                $relation->where('collections.id', $collection->id);
            });

            return;
        }

        $rules = $this->resolveCollectionRules($collection);

        if ($rules->isEmpty()) {
            return;
        }

        if ($this->shouldMatchAll($collection)) {
            $rules->each(function (array $rule) use ($query): void {
                $this->applyRuleConstraint($query, $rule, 'where');
            });

            return;
        }

        $query->where(function (Builder $outer) use ($rules): void {
            $rules->each(function (array $rule) use ($outer): void {
                $this->applyRuleConstraint($outer, $rule, 'orWhere');
            });
        });
    }

    /**
     * Normalize collection rules into simple arrays for downstream processing.
     *
     * @return Collection<int, array{field:string, operator:string, value:string|null}>
     */
    private function resolveCollectionRules(CollectionModel $collection): Collection
    {
        return $collection->rules()
            ->where('is_active', true)
            ->orderBy('position')
            ->get(['field', 'operator', 'value'])
            ->map(static function (CollectionRule $rule): array {
                return [
                    'field'    => (string) $rule->field,
                    'operator' => (string) $rule->operator,
                    'value'    => $rule->value,
                ];
            });
    }

    /**
     * Determine whether all rules must be satisfied simultaneously.
     */
    private function shouldMatchAll(CollectionModel $collection): bool
    {
        $matchConditions = $collection->match_conditions ?? null;

        if ($matchConditions instanceof BackedEnum) {
            return $matchConditions->value === 'all';
        }

        if (is_object($matchConditions) && property_exists($matchConditions, 'value')) {
            return $matchConditions->value === 'all';
        }

        if (is_string($matchConditions)) {
            return $matchConditions === 'all';
        }

        return true;
    }

    /**
     * Apply an individual rule constraint to the provided query builder.
     *
     * @param 'where'|'orWhere' $booleanMethod
     */
    private function applyRuleConstraint(Builder $query, array $rule, string $booleanMethod): void
    {
        $field = $rule['field'];
        $operator = $rule['operator'];
        $value = $rule['value'];

        if ($field === 'product_title') {
            $pattern = $this->resolveTitlePattern($operator, $value);

            if ($pattern === null) {
                return;
            }

            $query->{$booleanMethod}(static function (Builder $builder) use ($operator, $pattern): void {
                match ($operator) {
                    'contains', 'starts_with', 'ends_with' => $builder->where('name', 'like', $pattern),
                    'not_contains'  => $builder->where('name', 'not like', $pattern),
                    'equals_to'     => $builder->where('name', '=', $pattern),
                    'not_equals_to' => $builder->where('name', '!=', $pattern),
                    default         => null,
                };
            });

            return;
        }

        if ($field === 'product_price') {
            if (! $this->isSupportedPriceOperator($operator) || ! is_numeric($value)) {
                return;
            }

            $amount = (float) $value;

            $query->{$booleanMethod}(static function (Builder $builder) use ($operator, $amount): void {
                $builder->whereHas('prices', static function (Builder $priceQuery) use ($operator, $amount): void {
                    $priceQuery->whereRelation('currency', 'code', current_currency());

                    match ($operator) {
                        'less_than'     => $priceQuery->where('amount', '<', $amount),
                        'greater_than'  => $priceQuery->where('amount', '>', $amount),
                        'equals_to'     => $priceQuery->where('amount', '=', $amount),
                        'not_equals_to' => $priceQuery->where('amount', '!=', $amount),
                        default         => null,
                    };
                });
            });

            return;
        }

        if ($field === 'product_brand') {
            if (! is_numeric($value)) {
                return;
            }

            $query->{$booleanMethod}('brand_id', '=', (int) $value);

            return;
        }

        if ($field === 'product_category') {
            if (! is_numeric($value)) {
                return;
            }

            $categoryId = (int) $value;

            $query->{$booleanMethod}(static function (Builder $builder) use ($categoryId): void {
                $builder->whereHas('categories', static function (Builder $categoryQuery) use ($categoryId): void {
                    $categoryQuery->where('id', $categoryId);
                });
            });
        }
    }

    /**
     * Build the LIKE pattern for the requested operator.
     */
    private function resolveTitlePattern(string $operator, mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $value);

        return match ($operator) {
            'contains', 'not_contains' => "%{$escaped}%",
            'starts_with' => "{$escaped}%",
            'ends_with'   => "%{$escaped}",
            'equals_to', 'not_equals_to' => $escaped,
            default => null,
        };
    }

    /**
     * Identify the operators that support numeric price comparisons.
     */
    private function isSupportedPriceOperator(string $operator): bool
    {
        return in_array($operator, ['less_than', 'greater_than', 'equals_to', 'not_equals_to'], true);
    }
}
