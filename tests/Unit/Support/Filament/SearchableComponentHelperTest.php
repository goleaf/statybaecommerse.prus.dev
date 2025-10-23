<?php

declare(strict_types=1);

use App\Support\Filament\Components\SearchableComponentHelper;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Set;
use Filament\Schemas\Components\Component;

it('hydrates the searchable input with the normalised payload', function (): void {
    $component = SearchableInput::make('product_id');

    SearchableComponentHelper::hydrate(
        component: $component,
        state: 5,
        resolveRecord: static fn (int $identifier): array => [
            'id' => $identifier,
            'name' => 'Demo Product',
        ],
        normalizePayload: static fn (array $record): array => [
            'value' => $record['id'],
            'label' => $record['name'],
            'payload' => [
                'product_id' => $record['id'],
                'sku' => 'DEMO-001',
            ],
        ],
    );

    expect($component->getState())->toBe('5')
        ->and($component->getOptions())
        ->toMatchArray(['5' => 'Demo Product'])
        ->and($component->getPayload())
        ->toMatchArray([
            'product_id' => 5,
            'sku' => 'DEMO-001',
        ]);
});

it('syncs selected records and clears stale selections', function (): void {
    $component = SearchableInput::make('product_id');

    $set = new class extends Set {
        /** @var array<string, mixed> */
        public array $values = [];

        public function __construct()
        {
            parent::__construct(new class extends Component {
                protected string $view = 'filament-support::components.actions';
            });
        }

        public function __invoke(string | Component $path, mixed $state, bool $isAbsolute = false, bool $shouldCallUpdatedHooks = false): mixed
        {
            $this->values[(string) $path] = $state;

            return $state;
        }
    };

    $syncedPayload = [];
    $cleared = false;

    SearchableComponentHelper::syncSelectedRecord(
        component: $component,
        state: '42',
        set: $set,
        attribute: 'product_id',
        resolveRecord: static fn (string $identifier): array => [
            'id' => (int) $identifier,
            'name' => 'Inventory Widget',
        ],
        normalizePayload: static fn (array $record): array => [
            'value' => $record['id'],
            'label' => $record['name'],
            'payload' => [
                'product_id' => $record['id'],
                'price' => 19.99,
            ],
        ],
        onSync: static function (array $normalised) use (&$syncedPayload): void {
            $syncedPayload = $normalised['payload'];
        },
        onClear: static function () use (&$cleared): void {
            $cleared = true;
        },
    );

    expect($set->values['product_id'] ?? null)->toBe(42)
        ->and($component->getState())->toBe('42')
        ->and($component->getOptions())
        ->toMatchArray(['42' => 'Inventory Widget'])
        ->and($component->getPayload())
        ->toMatchArray([
            'product_id' => 42,
            'price' => 19.99,
        ])
        ->and($syncedPayload)
        ->toMatchArray([
            'product_id' => 42,
            'price' => 19.99,
        ])
        ->and($cleared)->toBeFalse();

    SearchableComponentHelper::syncSelectedRecord(
        component: $component,
        state: '',
        set: $set,
        attribute: 'product_id',
        resolveRecord: static fn (string $identifier): ?array => null,
        normalizePayload: static fn (array $record): array => $record,
        onSync: static function (): void {
            // No-op for the empty branch.
        },
        onClear: static function () use (&$cleared): void {
            $cleared = true;
        },
    );

    expect($set->values['product_id'] ?? null)->toBeNull()
        ->and($component->getState())->toBeNull()
        ->and($component->getOptions())->toBeEmpty()
        ->and($component->getPayload())
        ->toBe([])
        ->and($cleared)->toBeTrue();
});
