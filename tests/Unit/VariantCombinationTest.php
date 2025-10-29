<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\VariantCombination;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->product = Product::factory()->create([
        'name'       => 'Test Product',
        'is_enabled' => true,
    ]);

    $this->variantCombination = VariantCombination::factory()->create([
        'product_id'             => $this->product->id,
        'attribute_combinations' => [
            'color' => 'red',
            'size'  => 'large',
        ],
        'is_available' => true,
    ]);
});

describe('VariantCombination Model', function (): void {
    it('unit: can be created', function (): void {
        expect($this->variantCombination)->toBeInstanceOf(VariantCombination::class);
        expect($this->variantCombination->product_id)->toBe($this->product->id);
        expect($this->variantCombination->is_available)->toBeTrue();
    });

    it('unit: has correct fillable attributes', function (): void {
        $fillable = $this->variantCombination->getFillable();

        expect($fillable)->toContain('product_id');
        expect($fillable)->toContain('attribute_combinations');
        expect($fillable)->toContain('combination_hash');
        expect($fillable)->toContain('is_available');
    });

    it('unit: has correct casts', function (): void {
        $casts = $this->variantCombination->getCasts();

        expect($casts['attribute_combinations'])->toBe('array');
        expect($casts['is_available'])->toBe('boolean');
    });

    it('unit: has correct appends', function (): void {
        $appends = $this->variantCombination->getAppends();

        expect($appends)->toContain('formatted_combinations');
        expect($appends)->toContain('combination_hash');
        expect($appends)->toContain('is_valid_combination');
    });

    it('unit: belongs to a product', function (): void {
        expect($this->variantCombination->product)->toBeInstanceOf(Product::class);
        expect($this->variantCombination->product->id)->toBe($this->product->id);
    });

    it('unit: can format combinations correctly', function (): void {
        $formatted = $this->variantCombination->formatted_combinations;

        expect($formatted)->toContain('Color: red');
        expect($formatted)->toContain('Size: large');
    });

    it('unit: generates combination hash correctly', function (): void {
        $hash = $this->variantCombination->combination_hash;

        expect($hash)->toBeString();
        expect(strlen($hash))->toBe(64); // SHA-256 hash length
    });

    it('unit: validates combination correctly', function (): void {
        $isValid = $this->variantCombination->is_valid_combination;

        expect($isValid)->toBeBool();
    });

    it('unit: can scope available combinations', function (): void {
        $availableCombination = VariantCombination::factory()->create([
            'product_id'   => $this->product->id,
            'is_available' => true,
        ]);

        $unavailableCombination = VariantCombination::factory()->create([
            'product_id'   => $this->product->id,
            'is_available' => false,
        ]);

        $availableCombinations = VariantCombination::available()->get();

        expect($availableCombinations)->toContainModel($availableCombination);
        expect($availableCombinations->contains(fn ($item): bool => $item instanceof \Illuminate\Database\Eloquent\Model && $item->is($unavailableCombination)))->toBeFalse();
    });

    it('unit: can scope by product', function (): void {
        $anotherProduct = Product::factory()->create();
        $anotherCombination = VariantCombination::factory()->create([
            'product_id' => $anotherProduct->id,
        ]);

        $productCombinations = VariantCombination::byProduct($this->product->id)->get();

        expect($productCombinations)->toContainModel($this->variantCombination);
        expect($productCombinations->contains(fn ($item): bool => $item instanceof \Illuminate\Database\Eloquent\Model && $item->is($anotherCombination)))->toBeFalse();
    });

    it('unit: can scope by attribute value', function (): void {
        $redCombination = VariantCombination::factory()->create([
            'product_id'             => $this->product->id,
            'attribute_combinations' => ['color' => 'red'],
        ]);

        $blueCombination = VariantCombination::factory()->create([
            'product_id'             => $this->product->id,
            'attribute_combinations' => ['color' => 'blue'],
        ]);

        $redCombinations = VariantCombination::byAttributeValue('color', 'red')->get();

        expect($redCombinations)->toContainModel($redCombination);
        expect($redCombinations->contains(fn ($item): bool => $item instanceof \Illuminate\Database\Eloquent\Model && $item->is($blueCombination)))->toBeFalse();
    });

    it('unit: can scope by combination', function (): void {
        $redLargeCombination = VariantCombination::factory()->create([
            'product_id'             => $this->product->id,
            'attribute_combinations' => [
                'color' => 'red',
                'size'  => 'large',
            ],
        ]);

        $blueSmallCombination = VariantCombination::factory()->create([
            'product_id'             => $this->product->id,
            'attribute_combinations' => [
                'color' => 'blue',
                'size'  => 'small',
            ],
        ]);

        $redLargeCombinations = VariantCombination::byCombination([
            'color' => 'red',
            'size'  => 'large',
        ])->get();

        expect($redLargeCombinations)->toContainModel($redLargeCombination);
        expect($redLargeCombinations->contains(fn ($item): bool => $item instanceof \Illuminate\Database\Eloquent\Model && $item->is($blueSmallCombination)))->toBeFalse();
    });

    it('unit: can generate combinations for a product', function (): void {
        $product = Product::factory()->create();

        $colorAttribute = \App\Models\Attribute::factory()->create(['name' => 'color']);
        $sizeAttribute = \App\Models\Attribute::factory()->create(['name' => 'size']);

        \App\Models\AttributeValue::factory()->create([
            'attribute_id' => $colorAttribute->id,
            'value'        => 'red',
        ]);
        \App\Models\AttributeValue::factory()->create([
            'attribute_id' => $colorAttribute->id,
            'value'        => 'blue',
        ]);

        \App\Models\AttributeValue::factory()->create([
            'attribute_id' => $sizeAttribute->id,
            'value'        => 'small',
        ]);
        \App\Models\AttributeValue::factory()->create([
            'attribute_id' => $sizeAttribute->id,
            'value'        => 'large',
        ]);

        $product->attributes()->attach([$colorAttribute->id, $sizeAttribute->id]);

        $combinations = VariantCombination::generateCombinations($product);

        expect($combinations)->toBeArray();
        expect($combinations)->toHaveCount(4);
        expect($combinations)->toContain(['color' => 'blue', 'size' => 'large']);
    });

    it('unit: falls back to deterministic payloads when no attributes exist', function (): void {
        $product = Product::factory()->create();

        $combinations = VariantCombination::generateCombinations($product);

        expect($combinations)->toHaveCount(1);
        expect($combinations[0])->toBe([
            '__fallback' => 'product-' . $product->getKey(),
        ]);
    });

    it('unit: can create combinations for a product', function (): void {
        $product = Product::factory()->create();

        VariantCombination::createCombinationsForProduct($product);

        $combinations = VariantCombination::where('product_id', $product->id)->get();

        expect($combinations)->not->toBeEmpty();
    });

    it('unit: can find variant by combination', function (): void {
        $product = Product::factory()->create();
        $combination = ['color' => 'red', 'size' => 'large'];

        $variantCombination = VariantCombination::factory()->create([
            'product_id'             => $product->id,
            'attribute_combinations' => $combination,
        ]);

        $foundVariant = VariantCombination::findVariantByCombination($product, $combination);

        expect($foundVariant)->toBeNull(); // No actual variant exists, just combination
    });

    it('unit: can get available combinations for a product', function (): void {
        $product = Product::factory()->create();

        $availableCombination = VariantCombination::factory()->create([
            'product_id'   => $product->id,
            'is_available' => true,
        ]);

        $unavailableCombination = VariantCombination::factory()->create([
            'product_id'   => $product->id,
            'is_available' => false,
        ]);

        $availableCombinations = VariantCombination::getAvailableCombinations($product);

        expect($availableCombinations)->toBeArray();
    });

    it('unit: can be soft deleted', function (): void {
        $this->variantCombination->delete();

        expect($this->variantCombination->trashed())->toBeTrue();

        // Should still exist in database but soft deleted
        $this->assertDatabaseHas('variant_combinations', [
            'id' => $this->variantCombination->id,
        ]);
    });

    it('unit: can be restored from soft delete', function (): void {
        $this->variantCombination->delete();

        expect($this->variantCombination->trashed())->toBeTrue();

        $this->variantCombination->restore();

        expect($this->variantCombination->trashed())->toBeFalse();
    });

    it('unit: can be force deleted', function (): void {
        $combinationId = $this->variantCombination->id;

        $this->variantCombination->forceDelete();

        $this->assertDatabaseMissing('variant_combinations', [
            'id' => $combinationId,
        ]);
    });

    it('unit: has correct table name', function (): void {
        expect($this->variantCombination->getTable())->toBe('variant_combinations');
    });

    it('unit: can be replicated', function (): void {
        $replicated = $this->variantCombination->replicate();

        expect($replicated)->toBeInstanceOf(VariantCombination::class);
        expect($replicated->product_id)->toBe($this->variantCombination->product_id);
        expect($replicated->attribute_combinations)->toBe($this->variantCombination->attribute_combinations);
        expect($replicated->is_available)->toBe($this->variantCombination->is_available);
    });

    it('unit: handles empty attribute combinations', function (): void {
        $emptyCombination = VariantCombination::factory()->create([
            'product_id'             => $this->product->id,
            'attribute_combinations' => [],
        ]);

        expect($emptyCombination->formatted_combinations)->toBe('No combinations');
        expect($emptyCombination->combination_hash)->toBe(
            hash('sha256', 'fallback:' . $this->product->getKey())
        );
    });

    it('unit: handles null attribute combinations', function (): void {
        $nullCombination = VariantCombination::factory()->create([
            'product_id'             => $this->product->id,
            'attribute_combinations' => null,
        ]);

        expect($nullCombination->formatted_combinations)->toBe('No combinations');
        expect($nullCombination->combination_hash)->toBe(
            hash('sha256', 'fallback:' . $this->product->getKey())
        );
    });

    it('unit: normalises nested combination values for deterministic hashing', function (): void {
        $product = Product::factory()->create();

        $first = VariantCombination::factory()->create([
            'product_id'             => $product->id,
            'attribute_combinations' => [
                'material' => ['oak', 'pine'],
                'finish'   => ['matte', 'glossy'],
            ],
        ]);

        $second = VariantCombination::factory()->create([
            'product_id'             => $product->id,
            'attribute_combinations' => [
                'finish'   => ['glossy', 'matte'],
                'material' => ['pine', 'oak'],
            ],
        ]);

        // Persisted combinations should be normalised and sorted consistently for deterministic comparisons.
        expect($first->attribute_combinations)->toBe([
            'finish'   => ['glossy', 'matte'],
            'material' => ['oak', 'pine'],
        ]);
        // Confirm the second record reorders both top-level keys and nested lists while preserving hash stability.
        expect($second->attribute_combinations)->toBe([
            'finish'   => ['glossy', 'matte'],
            'material' => ['oak', 'pine'],
        ]);
        expect($first->combination_hash)->toBe($second->combination_hash);
    });

    it('unit: can be created with factory', function (): void {
        $combination = VariantCombination::factory()->create();

        expect($combination)->toBeInstanceOf(VariantCombination::class);
        expect($combination->product_id)->not->toBeNull();
        expect($combination->attribute_combinations)->toBeArray();
        expect($combination->is_available)->toBeBool();
    });

    it('unit: can be created with specific attributes', function (): void {
        $combination = VariantCombination::factory()->create([
            'product_id'             => $this->product->id,
            'attribute_combinations' => ['test' => 'value'],
            'is_available'           => false,
        ]);

        expect($combination->product_id)->toBe($this->product->id);
        expect($combination->attribute_combinations)->toBe(['test' => 'value']);
        expect($combination->is_available)->toBeFalse();
    });

    it('unit: hydrates cached combinations for strict model comparisons', function (): void {
        VariantCombination::refreshCombinationCacheForProduct($this->product->id);

        $cached = VariantCombination::cachedForProduct($this->product->id);

        expect($cached)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
        expect($cached)->toContainModel($this->variantCombination);

        $this->variantCombination->delete();

        $cachedAfterDelete = VariantCombination::cachedForProduct($this->product->id);

        expect($cachedAfterDelete->contains(fn ($item): bool => $item instanceof \Illuminate\Database\Eloquent\Model && $item->is($this->variantCombination)))->toBeFalse();
    });
});
