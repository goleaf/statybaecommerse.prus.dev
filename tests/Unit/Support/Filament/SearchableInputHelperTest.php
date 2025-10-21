<?php

declare(strict_types=1);

use App\Support\Filament\SearchableInputHelper;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Mockery as MockeryFacade;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

uses(MockeryPHPUnitIntegration::class);

test('hydrate helper populates the component state with resolved option data', function (): void {
    $component = MockeryFacade::mock(SearchableInput::class);

    $component
        ->shouldReceive('state')
        ->once()
        ->with('5')
        ->andReturnSelf();

    $component
        ->shouldReceive('options')
        ->once()
        ->with(['5' => 'Resolved label'])
        ->andReturnSelf();

    // The resolver mimics a search payload normaliser resolving the display label.
    SearchableInputHelper::hydrate(
        $component,
        5,
        static fn (int $value): ?array => [
            'value' => $value,
            'label' => 'Resolved label',
        ],
    );
});

test('hydrate helper clears the component when resolver cannot locate the record', function (): void {
    $component = MockeryFacade::mock(SearchableInput::class);

    $component
        ->shouldReceive('state')
        ->once()
        ->with(null)
        ->andReturnSelf();

    $component
        ->shouldReceive('options')
        ->once()
        ->with([])
        ->andReturnSelf();

    // Returning null emulates a resolver missing the identifier entirely.
    SearchableInputHelper::hydrate(
        $component,
        99,
        static fn (int $value): ?array => null,
    );
});

test('clear helper flushes dependent keys', function (): void {
    $calls = [];

    $set = function (string $field, mixed $value) use (&$calls): void {
        $calls[$field] = $value;
    };

    SearchableInputHelper::clear($set, [
        'product_id' => null,
        'name'       => 'Example',
    ]);

    expect($calls)->toBe([
        'product_id' => null,
        'name'       => 'Example',
    ]);
});
