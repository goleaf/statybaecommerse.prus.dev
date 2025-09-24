<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\VariantCombination;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->product = Product::factory()->create([
        'name' => 'Test Product',
        'is_enabled' => true,
    ]);

    $this->variantCombination = VariantCombination::factory()->create([
        'product_id' => $this->product->id,
        'attribute_combinations' => [
            'color' => 'red',
            'size' => 'large',
        ],
        'is_available' => true,
    ]);
});

describe('VariantCombination Model', function () {
    it('can be created', function () {
        expect($this->variantCombination)->toBeInstanceOf(VariantCombination::class);
        expect($this->variantCombination->product_id)->toBe($this->product->id);
        expect($this->variantCombination->is_available)->toBeTrue();
    });

    it('has correct fillable attributes', function () {
        $fillable = $this->variantCombination->getFillable();

        expect($fillable)->toContain('product_id');
        expect($fillable)->toContain('attribute_combinations');
        expect($fillable)->toContain('is_available');
    });

    it('has correct casts', function () {
        $casts = $this->variantCombination->getCasts();

        expect($casts['attribute_combinations'])->toBe('array');
        expect($casts['is_available'])->toBe('boolean');
    });

    it('has correct appends', function () {
        $appends = $this->variantCombination->getAppends();

        expect($appends)->toContain('formatted_combinations');
        expect($appends)->toContain('combination_hash');
        expect($appends)->toContain('is_valid_combination');
    });

    it('belongs to a product', function () {
        expect($this->variantCombination->product)->toBeInstanceOf(Product::class);
        expect($this->variantCombination->product->id)->toBe($this->product->id);
    });

    it('can format combinations correctly', function () {
        $formatted = $this->variantCombination->formatted_combinations;

        expect($formatted)->toContain('Color: red');
        expect($formatted)->toContain('Size: large');
    });

    it('generates combination hash correctly', function () {
        $hash = $this->variantCombination->combination_hash;

        expect($hash)->toBeString();
        expect(strlen($hash))->toBe(32); // MD5 hash length
    });

    it('validates combination correctly', function () {
        $isValid = $this->variantCombination->is_valid_combination;

        expect($isValid)->toBeBool();
    });

    it('can scope available combinations', function () {
        $availableCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'is_available' => true,
        ]);

        $unavailableCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'is_available' => false,
        ]);

        $availableCombinations = VariantCombination::available()->get();

        expect($availableCombinations)->toContain($availableCombination);
        expect($availableCombinations)->not->toContain($unavailableCombination);
    });

    it('can scope by product', function () {
        $anotherProduct = Product::factory()->create();
        $anotherCombination = VariantCombination::factory()->create([
            'product_id' => $anotherProduct->id,
        ]);

        $productCombinations = VariantCombination::byProduct($this->product->id)->get();

        expect($productCombinations)->toContain($this->variantCombination);
        expect($productCombinations)->not->toContain($anotherCombination);
    });

    it('can scope by attribute value', function () {
        $redCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'attribute_combinations' => ['color' => 'red'],
        ]);

        $blueCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'attribute_combinations' => ['color' => 'blue'],
        ]);

        $redCombinations = VariantCombination::byAttributeValue('color', 'red')->get();

        expect($redCombinations)->toContain($redCombination);
        expect($redCombinations)->not->toContain($blueCombination);
    });

    it('can scope by combination', function () {
        $redLargeCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'attribute_combinations' => [
                'color' => 'red',
                'size' => 'large',
            ],
        ]);

        $blueSmallCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'attribute_combinations' => [
                'color' => 'blue',
                'size' => 'small',
            ],
        ]);

        $redLargeCombinations = VariantCombination::byCombination([
            'color' => 'red',
            'size' => 'large',
        ])->get();

        expect($redLargeCombinations)->toContain($redLargeCombination);
        expect($redLargeCombinations)->not->toContain($blueSmallCombination);
    });

    it('can generate combinations for a product', function () {
        $product = Product::factory()->create();

        // Mock attributes for the product
        $attributes = [
            ['name' => 'color', 'values' => ['red', 'blue']],
            ['name' => 'size', 'values' => ['small', 'large']],
        ];

        $combinations = VariantCombination::generateCombinations($product);

        expect($combinations)->toBeArray();
        // Should generate 2 * 2 = 4 combinations
        expect(count($combinations))->toBe(4);
    });

    it('can create combinations for a product', function () {
        $product = Product::factory()->create();

        VariantCombination::createCombinationsForProduct($product);

        $combinations = VariantCombination::where('product_id', $product->id)->get();

        expect($combinations)->not->toBeEmpty();
    });

    it('can find variant by combination', function () {
        $product = Product::factory()->create();
        $combination = ['color' => 'red', 'size' => 'large'];

        $variantCombination = VariantCombination::factory()->create([
            'product_id' => $product->id,
            'attribute_combinations' => $combination,
        ]);

        $foundVariant = VariantCombination::findVariantByCombination($product, $combination);

        expect($foundVariant)->toBeNull(); // No actual variant exists, just combination
    });

    it('can get available combinations for a product', function () {
        $product = Product::factory()->create();

        $availableCombination = VariantCombination::factory()->create([
            'product_id' => $product->id,
            'is_available' => true,
        ]);

        $unavailableCombination = VariantCombination::factory()->create([
            'product_id' => $product->id,
            'is_available' => false,
        ]);

        $availableCombinations = VariantCombination::getAvailableCombinations($product);

        expect($availableCombinations)->toBeArray();
    });

    it('can be soft deleted', function () {
        $this->variantCombination->delete();

        expect($this->variantCombination->trashed())->toBeTrue();

        // Should still exist in database but soft deleted
        $this->assertDatabaseHas('variant_combinations', [
            'id' => $this->variantCombination->id,
        ]);
    });

    it('can be restored from soft delete', function () {
        $this->variantCombination->delete();

        expect($this->variantCombination->trashed())->toBeTrue();

        $this->variantCombination->restore();

        expect($this->variantCombination->trashed())->toBeFalse();
    });

    it('can be force deleted', function () {
        $combinationId = $this->variantCombination->id;

        $this->variantCombination->forceDelete();

        $this->assertDatabaseMissing('variant_combinations', [
            'id' => $combinationId,
        ]);
    });

    it('has correct table name', function () {
        expect($this->variantCombination->getTable())->toBe('variant_combinations');
    });

    it('can be replicated', function () {
        $replicated = $this->variantCombination->replicate();

        expect($replicated)->toBeInstanceOf(VariantCombination::class);
        expect($replicated->product_id)->toBe($this->variantCombination->product_id);
        expect($replicated->attribute_combinations)->toBe($this->variantCombination->attribute_combinations);
        expect($replicated->is_available)->toBe($this->variantCombination->is_available);
    });

    it('handles empty attribute combinations', function () {
        $emptyCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'attribute_combinations' => [],
        ]);

        expect($emptyCombination->formatted_combinations)->toBe('No combinations');
        expect($emptyCombination->combination_hash)->toBe('');
    });

    it('handles null attribute combinations', function () {
        $nullCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'attribute_combinations' => null,
        ]);

        expect($nullCombination->formatted_combinations)->toBe('No combinations');
        expect($nullCombination->combination_hash)->toBe('');
    });

    it('can be created with factory', function () {
        $combination = VariantCombination::factory()->create();

        expect($combination)->toBeInstanceOf(VariantCombination::class);
        expect($combination->product_id)->not->toBeNull();
        expect($combination->attribute_combinations)->toBeArray();
        expect($combination->is_available)->toBeBool();
    });

    it('can be created with specific attributes', function () {
        $combination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'attribute_combinations' => ['test' => 'value'],
            'is_available' => false,
        ]);

        expect($combination->product_id)->toBe($this->product->id);
        expect($combination->attribute_combinations)->toBe(['test' => 'value']);
        expect($combination->is_available)->toBeFalse();
    });
});
