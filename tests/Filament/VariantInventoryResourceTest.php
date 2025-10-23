<?php

declare(strict_types=1);

use App\Filament\Resources\VariantInventoryResource;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

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
