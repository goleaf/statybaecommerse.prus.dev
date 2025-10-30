<?php

declare(strict_types=1);

use App\Filament\Resources\VariantInventoryResource;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use ReflectionMethod;

// Bridge the new Filament namespace layout so the resource signature resolves during tests.
if (! class_exists(\Filament\Forms\Form::class)) {
    class_alias(\Filament\Schemas\Components\Form::class, \Filament\Forms\Form::class);
}

if (! class_exists(\Filament\Forms\Set::class)) {
    class_alias(\Filament\Schemas\Components\Utilities\Set::class, \Filament\Forms\Set::class);
}

uses()->group('filament');

it('clears lookup payload when the variant and location inputs are emptied', function (): void {
    $form = VariantInventoryResource::form(Schema::make());

    $components = $form->getFlatComponents(withActions: false, withHidden: true);

    expect($components)->toHaveKeys(['variant_id', 'variant_payload', 'location_id', 'location_payload']);

    /** @var SearchableInput|Component $variantInput */
    $variantInput = $components['variant_id'];
    /** @var SearchableInput|Component $locationInput */
    $locationInput = $components['location_id'];
    /** @var Component $variantPayload */
    $variantPayload = $components['variant_payload'];
    /** @var Component $locationPayload */
    $locationPayload = $components['location_payload'];

    expect($variantInput)->toBeInstanceOf(SearchableInput::class)
        ->and($locationInput)->toBeInstanceOf(SearchableInput::class);

    // Seed non-empty state so we can assert that the helper wipes both identifiers and metadata arrays.
    $variantInput->state('321');
    $locationInput->state('654');
    $variantPayload->state(['preset' => 'variant']);
    $locationPayload->state(['preset' => 'location']);

    // Clearing each lookup should funnel through SearchableComponentHelper::clear() and reset dependencies.
    $variantInput->state('');
    $variantInput->callAfterStateUpdated();

    $locationInput->state('');
    $locationInput->callAfterStateUpdated();

    expect($variantInput->getState())->toBeNull()
        ->and($variantPayload->getState())->toBeArray()->toBeEmpty()
        ->and($locationInput->getState())->toBeNull()
        ->and($locationPayload->getState())->toBeArray()->toBeEmpty();
});

it('normalises the variant payload including related product metadata', function (): void {
    // Prepare a product variant with numeric-friendly attributes to verify casting behaviour.
    $variant = ProductVariant::make([
        'product_id' => 654,
        'sku'        => 1001,
        'name'       => 'Wool Hat',
        'price'      => '199.95',
    ]);
    $variant->setAttribute('id', 321);

    // Attach a related product so the helper can surface parent identifiers in the payload.
    $product = Product::make([
        'sku'  => 'PROD-900',
        'name' => 'Felt Collection',
    ]);
    $product->setAttribute('id', 654);
    $variant->setRelation('product', $product);

    // Reflect the private helper so we can assert on the exact metadata structure it emits.
    $method = new ReflectionMethod(VariantInventoryResource::class, 'normaliseVariantPayload');
    $method->setAccessible(true);

    /** @var array<string, mixed> $payload */
    $payload = $method->invoke(null, $variant);

    expect($payload)
        ->toMatchArray([
            'variant_id'   => 321,
            'sku'          => '1001',
            'name'         => 'Wool Hat',
            'price'        => 199.95,
            'product_id'   => 654,
            'product_sku'  => 'PROD-900',
            'product_name' => 'Felt Collection',
        ]);
});

it('casts arbitrary location attributes into the expected payload shape', function (): void {
    // Seed a location with mixed attribute types so we can validate the string coercion logic.
    $location = Location::make([
        'name'         => 'Central Warehouse',
        'code'         => 42,
        'city'         => 'Vilnius',
        'country_code' => 123,
    ]);
    $location->setAttribute('id', 777);

    // Invoke the normaliser through reflection to reach the otherwise private helper.
    $method = new ReflectionMethod(VariantInventoryResource::class, 'normaliseLocationPayload');
    $method->setAccessible(true);

    /** @var array<string, mixed> $payload */
    $payload = $method->invoke(null, $location);

    expect($payload)
        ->toMatchArray([
            'location_id'  => 777,
            'name'         => 'Central Warehouse',
            'code'         => '42',
            'city'         => 'Vilnius',
            'country_code' => '123',
        ]);
});
