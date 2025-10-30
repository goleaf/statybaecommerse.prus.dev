<?php

declare(strict_types=1);

use App\Support\FilamentCompat\Schemas\Components\Grid as CompatGrid;
use App\Support\FilamentCompat\Schemas\Components\Section as CompatSection;
use Filament\Forms\Components\TextInput;

it('exposes section children when configured through schema helper', function (): void {
    // Arrange: create a section with child components declared via the schema helper.
    $section = CompatSection::make('Example Section')
        ->schema([
            TextInput::make('name')
                ->label('Name field'),
        ]);

    // Act: resolve the flattened children from the compatibility bridge.
    $components = $section->getComponents();

    // Assert: the bridge returns the nested component in a plain array for legacy inspectors.
    expect($components)
        ->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(TextInput::class)
        ->and($components[0]->getName())->toBe('name');
});

it('exposes section children when configured through the components helper', function (): void {
    // Arrange: declare the children with the components helper to emulate legacy usage.
    $section = CompatSection::make('Example Section')
        ->components([
            TextInput::make('description')
                ->label('Description field'),
        ]);

    // Act: fetch the flattened children so assertion helpers receive the expected structure.
    $components = $section->getComponents();

    // Assert: the compatibility layer returns the declared component directly.
    expect($components)
        ->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(TextInput::class)
        ->and($components[0]->getName())->toBe('description');
});

it('flattens grid schema wrappers into raw child components', function (): void {
    // Arrange: wrap a single field inside the schema helper to mimic production resource usage.
    $grid = CompatGrid::make(2)
        ->schema([
            TextInput::make('sku')
                ->label('SKU field'),
        ]);

    // Act: read the compatibility bridge output that older assertions still expect.
    $components = $grid->getComponents();

    // Assert: the flattened output contains the child field without the intermediate schema object.
    expect($components)
        ->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(TextInput::class)
        ->and($components[0]->getName())->toBe('sku');
});

it('respects grid components assigned through the components helper', function (): void {
    // Arrange: configure the grid with components assigned directly through the compatibility helper.
    $grid = CompatGrid::make(2)
        ->components([
            TextInput::make('price')
                ->label('Price field'),
        ]);

    // Act: obtain the flattened child components to mirror runtime traversal behaviour.
    $components = $grid->getComponents();

    // Assert: the bridge preserves the declared component array for consumers that read raw components.
    expect($components)
        ->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(TextInput::class)
        ->and($components[0]->getName())->toBe('price');
});
