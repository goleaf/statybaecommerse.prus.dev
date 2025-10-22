<?php

declare(strict_types=1);

use App\Support\Filament\SearchableComponentHelper;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Schemas\Schema;
use Tests\Fixtures\FakeFilamentComponent;

/**
 * Helper to bootstrap standalone SearchableInput instances with a fake Filament container.
 */
function attachFakeFilamentContainer(SearchableInput $component): void
{
    // Attach the component to a lightweight schema so the typed container/livewire
    // dependencies introduced in Filament v4 are initialised for unit testing.
    $schema = Schema::make(new FakeFilamentComponent())
        ->schema([
            $component,
        ]);

    // Trigger component evaluation which applies the container relationship.
    $schema->getComponents();
}

it('hydrates the searchable component with the canonical payload tuple', function (): void {
    // Arrange: prime the component with a fake record that exposes metadata.
    $component = SearchableInput::make('product_id');
    attachFakeFilamentContainer($component);

    SearchableComponentHelper::hydrate(
        $component,
        42,
        static fn (int $identifier): ?array => [
            'id'    => $identifier,
            'label' => 'Example Product',
            'sku'   => 'SKU-42',
        ],
        static function (array $record): array {
            return [
                'value'   => $record['id'],
                'label'   => $record['label'],
                'payload' => [
                    'sku' => $record['sku'],
                ],
            ];
        },
    );

    // Assert: the helper persists state/options and injects canonical payload keys.
    expect($component->getState())->toBe('42');
    expect($component->getOptions())->toBe(['42' => 'Example Product']);
    expect($component->getPayload())->toBe([
        'sku'   => 'SKU-42',
        'id'    => '42',
        'label' => 'Example Product',
    ]);
});

it('synchronises identifiers and payload metadata when selections change', function (): void {
    // Arrange: mimic Filament\Forms\Set with a closure capturing field updates.
    $component = SearchableInput::make('product_id');
    attachFakeFilamentContainer($component);
    $fields = [
        'product_id'      => null,
        'product_payload' => ['id' => null, 'label' => '', 'sku' => null],
        'cleared'         => false,
    ];

    $set = static function (string $field, mixed $value) use (&$fields): void {
        $fields[$field] = $value;
    };

    // Act: sync a valid selection.
    SearchableComponentHelper::syncSelectedRecord(
        $component,
        '55',
        $set,
        'product_id',
        static fn (string $identifier): ?array => [
            'id'    => (int) $identifier,
            'label' => 'Cached Product',
            'sku'   => 'SYNC-55',
        ],
        static function (array $record): array {
            return [
                'value'   => $record['id'],
                'label'   => $record['label'],
                'payload' => [
                    'sku' => $record['sku'],
                ],
            ];
        },
        'product_payload',
        ['id' => null, 'label' => '', 'sku' => null],
        static function () use (&$fields): void {
            $fields['cleared'] = true;
        },
    );

    // Assert: the identifier is normalised, payload cached, and UI updated.
    expect($fields['product_id'])->toBe(55);
    expect($fields['product_payload'])->toBe([
        'sku'   => 'SYNC-55',
        'id'    => '55',
        'label' => 'Cached Product',
    ]);
    expect($component->getState())->toBe('55');
    expect($component->getOptions())->toBe(['55' => 'Cached Product']);
    expect($component->getPayload())->toBe([
        'sku'   => 'SYNC-55',
        'id'    => '55',
        'label' => 'Cached Product',
    ]);

    // Act: clearing the lookup should reset everything and trigger the clean-up callback.
    SearchableComponentHelper::syncSelectedRecord(
        $component,
        null,
        $set,
        'product_id',
        static fn (): ?array => null,
        static fn (): array => [
            'value'   => null,
            'label'   => null,
            'payload' => [],
        ],
        'product_payload',
        ['id' => null, 'label' => '', 'sku' => null],
        static function () use (&$fields): void {
            $fields['cleared'] = true;
        },
    );

    // Assert: the helper clears identifiers, payloads, and emits the clean-up callback once.
    expect($fields['product_id'])->toBeNull();
    expect($fields['product_payload'])->toBe([
        'id'    => null,
        'label' => '',
        'sku'   => null,
    ]);
    expect($fields['cleared'])->toBeTrue();
    expect($component->getState())->toBeNull();
    expect($component->getOptions())->toBe([]);
    expect($component->getPayload())->toBe([]);
});
