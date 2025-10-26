<?php

declare(strict_types=1);

use App\Livewire\Pages\Collection\Show;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Collection as CollectionModel;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAttributeValue;
use App\Services\ProductVariantAttributeMatrixService;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Build a collection-ready product without triggering brand factory inserts.
 */
function createCollectionProduct(array $overrides = []): Product
{
    // Generate a variable product in memory so associated factories remain optional.
    $product = Product::factory()->make(array_merge([
        'type'         => 'variable',
        'status'       => 'published',
        'is_visible'   => true,
        'is_enabled'   => true,
        'published_at' => now()->subDay(),
    ], $overrides));

    // Persist using unguarded mode to honour the factory payload verbatim.
    return Product::unguarded(static function () use ($product): Product {
        /** @var Product $instance */
        $instance = Product::create($product->getAttributes());

        return $instance->fresh();
    });
}

it('renders attribute filter options without triggering template errors', function (): void {
    // Arrange: create a visible collection that should expose storefront filters.
    $collection = CollectionModel::factory()->create([
        'show_filters' => true,
        'is_visible'   => true,
        'is_active'    => true,
        'is_enabled'   => true,
    ]);

    // Arrange: prepare a filterable attribute with predictable option labels.
    $attribute = Attribute::factory()->create([
        'name'          => 'Wood Finish',
        'slug'          => 'wood-finish',
        'type'          => 'select',
        'is_filterable' => true,
        'is_visible'    => true,
        'is_enabled'    => true,
        'is_active'     => true,
    ]);

    // Arrange: seed two attribute values so the filter widget has multiple choices to render.
    $oakValue = AttributeValue::factory()->for($attribute)->create([
        'value'         => 'Oak',
        'display_value' => 'Oak',
        'slug'          => 'oak',
        'sort_order'    => 1,
    ]);

    $walnutValue = AttributeValue::factory()->for($attribute)->create([
        'value'         => 'Walnut',
        'display_value' => 'Walnut',
        'slug'          => 'walnut',
        'sort_order'    => 2,
    ]);

    // Arrange: publish a product and link it to the collection so Livewire can query a catalog.
    $product = createCollectionProduct([
        'name' => 'Handcrafted Table',
        'slug' => Str::slug('Handcrafted Table ' . Str::random(4)),
    ]);
    $product->collections()->attach($collection->getKey());

    // Arrange: create two variants that reference the seeded attribute values for filtering.
    $variantOne = ProductVariant::factory()->for($product)->create([
        'sku'                      => 'TABLE-OAK-' . Str::random(5),
        'variant_attribute_matrix' => ['attribute_' . $attribute->getKey() => $oakValue->getKey()],
        'is_default'               => true,
        'is_enabled'               => true,
    ]);

    $variantTwo = ProductVariant::factory()->for($product)->create([
        'sku'                      => 'TABLE-WALNUT-' . Str::random(5),
        'variant_attribute_matrix' => ['attribute_' . $attribute->getKey() => $walnutValue->getKey()],
        'is_default'               => false,
        'is_enabled'               => true,
    ]);

    // Ensure pivot records exist even when simplified schemas omit triggers used in production.
    ProductVariantAttributeMatrixService::sync($variantOne->fresh(), ['attribute_' . $attribute->getKey() => $oakValue->getKey()]);
    ProductVariantAttributeMatrixService::sync($variantTwo->fresh(), ['attribute_' . $attribute->getKey() => $walnutValue->getKey()]);

    // Assert: confirm the pivot tables recorded the variant attribute mappings.
    expect(VariantAttributeValue::count())->toBeGreaterThan(0);

    // Act: render the Livewire collection page with the prepared dataset.
    $component = Livewire::test(Show::class, ['collection' => $collection]);

    $selectedValues = collect();

    // Assert: mimic the component query to confirm the option payload contains both attribute values.
    /** @var \Illuminate\Support\Collection<int, array{attribute: Attribute, values:\Illuminate\Support\Collection<int, AttributeValue>}> $options */
    $options = Product::query()
        ->where('is_visible', true)
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->whereHas('collections', static function ($relation) use ($collection): void {
            $relation->where('collections.id', $collection->getKey());
        })
        ->with(['variants.values.attribute'])
        ->get()
        ->flatMap(static function (Product $product) use ($selectedValues): array {
            $aggregated = [];

            foreach ($product->variants as $variant) {
                foreach ($variant->values as $value) {
                    $attribute = $value->attribute;

                    if ($attribute === null) {
                        continue;
                    }

                    $attributeId = (int) $attribute->getKey();

                    if (! array_key_exists($attributeId, $aggregated)) {
                        $aggregated[$attributeId] = [
                            'attribute' => $attribute,
                            'values'    => [],
                        ];
                    }

                    // Keep the transient selected flag in parity with the component logic.
                    $value->setAttribute('selected', $selectedValues->contains((int) $value->getKey()));

                    $aggregated[$attributeId]['values'][$value->getKey()] = $value;
                }
            }

            return $aggregated;
        })
        ->pipe(static fn (\Illuminate\Support\Collection $options): \Illuminate\Support\Collection => $options
            ->map(static function (array $option): array {
                $option['values'] = collect($option['values'])->values();

                return $option;
            })
            ->values());
    $attributeOption = $options->firstWhere(static function (array $option) use ($attribute): bool {
        return $option['attribute']?->getKey() === $attribute->getKey();
    });

    expect($attributeOption)->not->toBeNull();

    $values = $attributeOption['values'] ?? null;

    expect($values)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($values->contains(static function (AttributeValue $value) use ($oakValue): bool {
        return $value->getKey() === $oakValue->getKey();
    }))->toBeTrue();
    expect($values->contains(static function (AttributeValue $value) use ($walnutValue): bool {
        return $value->getKey() === $walnutValue->getKey();
    }))->toBeTrue();
    expect($values->every(static fn (AttributeValue $value): bool => $value->getAttribute('selected') !== null))->toBeTrue();

    // Assert: ensure the rendered template includes the attribute label, signalling no runtime errors.
    $component->assertSee($attribute->name);
    $component->assertSee($oakValue->display_value ?? $oakValue->value);
    $component->assertSee($walnutValue->display_value ?? $walnutValue->value);
});
