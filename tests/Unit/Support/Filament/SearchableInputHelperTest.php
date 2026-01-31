<?php

declare(strict_types=1);

use App\Support\Filament\Components\SearchableInput;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\SearchResult;
use Illuminate\Support\Collection;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as MockeryFacade;

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

    $component
        ->shouldReceive('payload')
        ->once()
        ->with([
            'foo'   => 'bar',
            'id'    => '5',
            'label' => 'Resolved label',
        ])
        ->andReturnSelf();

    // The resolver mimics a search payload normaliser resolving the display label.
    SearchableInputHelper::hydrate(
        $component,
        5,
        static fn (int $value): ?array => [
            'value'   => $value,
            'label'   => 'Resolved label',
            'payload' => ['foo' => 'bar'],
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

    $component
        ->shouldReceive('payload')
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

    $component = MockeryFacade::mock(SearchableInput::class);

    $component
        ->shouldReceive('getStatePath')
        ->once()
        ->andReturn('product_id');

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

    $component
        ->shouldReceive('payload')
        ->once()
        ->with([])
        ->andReturnSelf();

    SearchableInputHelper::clear($component, $set, [
        'product_id' => null,
        'name'       => 'Example',
    ]);

    expect($calls)->toMatchArray([
        'product_id'         => null,
        'product_id_payload' => [],
        'name'               => 'Example',
    ]);
});

test('hydrate helper supports search result dto payloads', function (): void {
    $component = MockeryFacade::mock(SearchableInput::class);

    $component
        ->shouldReceive('state')
        ->once()
        ->with('42')
        ->andReturnSelf();

    $component
        ->shouldReceive('options')
        ->once()
        ->with(['42' => 'Example label'])
        ->andReturnSelf();

    $component
        ->shouldReceive('payload')
        ->once()
        ->with([
            'id'    => '42',
            'label' => 'Example label',
        ])
        ->andReturnSelf();

    SearchableInputHelper::hydrate(
        $component,
        42,
        static function (int $value): SearchResult {
            return SearchResult::make('42', 'Example label')
                ->withData('payload', [
                    'id'    => $value,
                    'label' => 'Example label',
                ]);
        },
    );
});

test('hydrate helper can flatten arrayable labels and payloads', function (): void {
    $component = MockeryFacade::mock(SearchableInput::class);

    $component
        ->shouldReceive('state')
        ->once()
        ->with('5')
        ->andReturnSelf();

    $component
        ->shouldReceive('options')
        ->once()
        ->with(['5' => 'Foo Bar'])
        ->andReturnSelf();

    $component
        ->shouldReceive('payload')
        ->once()
        ->with([
            'foo'   => 'bar',
            'id'    => '5',
            'label' => 'Foo Bar',
        ])
        ->andReturnSelf();

    SearchableInputHelper::hydrate(
        $component,
        5,
        static fn (): Collection => Collection::make([
            'value'   => 5,
            'label'   => Collection::make(['Foo', 'Bar']),
            'payload' => Collection::make(['foo' => 'bar']),
        ]),
    );
});
