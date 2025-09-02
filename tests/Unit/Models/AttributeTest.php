<?php declare(strict_types=1);

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;

it('can create an attribute', function () {
    $attribute = Attribute::factory()->create([
        'name' => 'Color',
        'slug' => 'color',
        'type' => 'select',
        'is_required' => true,
        'is_filterable' => true,
        'is_searchable' => false,
        'sort_order' => 1,
    ]);

    expect($attribute->name)->toBe('Color');
    expect($attribute->slug)->toBe('color');
    expect($attribute->type)->toBe('select');
    expect($attribute->is_required)->toBeTrue();
    expect($attribute->is_filterable)->toBeTrue();
    expect($attribute->is_searchable)->toBeFalse();
    expect($attribute->sort_order)->toBe(1);
});

it('has attribute values relationship', function () {
    $attribute = Attribute::factory()->create();
    $value1 = AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'value' => 'Red']);
    $value2 = AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'value' => 'Blue']);

    expect($attribute->values)->toHaveCount(2);
    expect($attribute->values->pluck('value'))->toContain('Red', 'Blue');
});

it('has products relationship through pivot', function () {
    $attribute = Attribute::factory()->create();
    $product = Product::factory()->create();
    
    $product->attributes()->attach($attribute->id);

    expect($attribute->products)->toHaveCount(1);
    expect($attribute->products->first()->id)->toBe($product->id);
});

it('can be filtered by type', function () {
    $textAttribute = Attribute::factory()->create(['type' => 'text']);
    $selectAttribute = Attribute::factory()->create(['type' => 'select']);

    $textAttributes = Attribute::where('type', 'text')->get();
    $selectAttributes = Attribute::where('type', 'select')->get();

    expect($textAttributes)->toHaveCount(1);
    expect($textAttributes->first()->id)->toBe($textAttribute->id);
    expect($selectAttributes)->toHaveCount(1);
    expect($selectAttributes->first()->id)->toBe($selectAttribute->id);
});

it('can be filtered by required status', function () {
    $requiredAttribute = Attribute::factory()->create(['is_required' => true]);
    $optionalAttribute = Attribute::factory()->create(['is_required' => false]);

    $requiredAttributes = Attribute::where('is_required', true)->get();

    expect($requiredAttributes)->toHaveCount(1);
    expect($requiredAttributes->first()->id)->toBe($requiredAttribute->id);
});

it('can be filtered by filterable status', function () {
    $filterableAttribute = Attribute::factory()->create(['is_filterable' => true]);
    $nonFilterableAttribute = Attribute::factory()->create(['is_filterable' => false]);

    $filterableAttributes = Attribute::where('is_filterable', true)->get();

    expect($filterableAttributes)->toHaveCount(1);
    expect($filterableAttributes->first()->id)->toBe($filterableAttribute->id);
});

it('can be ordered by sort_order', function () {
    $attribute1 = Attribute::factory()->create(['sort_order' => 3, 'name' => 'Third']);
    $attribute2 = Attribute::factory()->create(['sort_order' => 1, 'name' => 'First']);
    $attribute3 = Attribute::factory()->create(['sort_order' => 2, 'name' => 'Second']);

    $orderedAttributes = Attribute::orderBy('sort_order')->get();

    expect($orderedAttributes->first()->name)->toBe('First');
    expect($orderedAttributes->get(1)->name)->toBe('Second');
    expect($orderedAttributes->last()->name)->toBe('Third');
});

it('validates required fields', function () {
    expect(fn() => Attribute::create([]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

it('validates unique slug', function () {
    Attribute::factory()->create(['slug' => 'existing-slug']);

    expect(fn() => Attribute::create([
        'name' => 'Test Attribute',
        'slug' => 'existing-slug',
        'type' => 'text',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});
