<?php

declare(strict_types=1);

use App\Support\Forms\MatrixFactory;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Schema;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Components\Component;
use Filament\Support\Contracts\TranslatableContentDriver;
use Livewire\Component as LivewireComponent;
use Filament\Schemas\Components\Utilities\Get;
use LaraZeus\MatrixChoice\Components\Matrix;

it('builds a permissions section with module toggle grids', function (): void {
    $definition = [
        'orders' => [
            'view' => 'orders.view',
            'edit' => 'orders.edit',
        ],
        'customers' => [
            'view' => 'customers.view',
        ],
    ];

    $section = MatrixFactory::permissions(
        definition: $definition,
        moduleLabelResolver: fn (string $module): string => strtoupper($module),
        abilityLabelResolver: fn (string $ability): string => ucfirst($ability),
        sectionLabel: 'Permissions',
        statePath: 'custom_permissions',
    );

    expect($section)->toBeInstanceOf(Section::class);

    $evaluatedSection = evaluate_schema_components($section)[0];

    expect($evaluatedSection)
        ->toBeInstanceOf(Section::class)
        ->and($evaluatedSection->getHeading())->toBe('Permissions')
        ->and($evaluatedSection->getStatePath())->toBe('custom_permissions');

    $moduleSections = $evaluatedSection->getChildComponents();

    expect($moduleSections)
        ->toHaveCount(2);

    $ordersSection = $moduleSections[0];
    expect($ordersSection)
        ->toBeInstanceOf(Section::class)
        ->and($ordersSection->getHeading())->toBe('ORDERS');

    $ordersGrid = $ordersSection->getChildComponents()[0];

    expect($ordersGrid)
        ->toBeInstanceOf(Grid::class);

    $orderToggles = $ordersGrid->getChildComponents();

    expect($orderToggles)
        ->toHaveCount(2)
        ->and($orderToggles[0])->toBeInstanceOf(Toggle::class)
        ->and($orderToggles[0]->getLabel())->toBe('View')
        ->and($orderToggles[1])->toBeInstanceOf(Toggle::class)
        ->and($orderToggles[1]->getLabel())->toBe('Edit');
});

it('builds a radio grid for attribute selection', function (): void {
    $grid = MatrixFactory::radioGrid(
        'attributes',
        fn (Get $get): array => [
            [
                'key' => 'attribute_1',
                'label' => 'Color',
                'options' => ['red' => 'Red', 'blue' => 'Blue'],
            ],
        ],
    );

    expect($grid)
        ->toBeInstanceOf(Grid::class);

    $schema = evaluate_grid_schema($grid);

    expect($schema)
        ->toHaveCount(1);

    $field = $schema[0];

    expect($field)
        ->toBeInstanceOf(Radio::class)
        ->and($field->getStatePath())->toBe('attributes.attribute_1')
        ->and($field->getOptions())
            ->toMatchArray(['red' => 'Red', 'blue' => 'Blue']);
});

it('renders a placeholder when no radio rows exist', function (): void {
    $grid = MatrixFactory::radioGrid('attributes', fn (Get $get): array => []);

    $schema = evaluate_grid_schema($grid);

    expect($schema)
        ->toHaveCount(1)
        ->and($schema[0])
            ->toBeInstanceOf(Placeholder::class)
            ->and($schema[0]->getLabel())
            ->toBe(__('No attributes available'));
});

it('builds a checkbox matrix using the Zeus component', function (): void {
    $matrix = MatrixFactory::checkboxGrid(
        'shipping_matrix',
        ['domestic' => 'Domestic', 'international' => 'International'],
        ['ground' => 'Ground', 'air' => 'Air'],
    );

    expect($matrix)
        ->toBeInstanceOf(Matrix::class)
        ->and($matrix->getRowData())
            ->toMatchArray([
                'domestic' => 'Domestic',
                'international' => 'International',
            ])
        ->and($matrix->getColumnData())
            ->toMatchArray([
                'ground' => 'Ground',
                'air' => 'Air',
            ])
        ->and($matrix->getPilColor())
            ->toBe('checkbox');
});

/**
 * @return array<int, mixed>
 */
function evaluate_grid_schema(Grid $grid): array
{
    return evaluate_schema_components($grid)[0]->getChildComponents();
}

/**
 * @return array<int, Component>
 */
function evaluate_schema_components(Component $component): array
{
    // Wrap the component in a temporary schema so Filament assigns an evaluation container before retrieving child components.
    return Schema::make(new class extends LivewireComponent implements HasSchemas {
        public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
        {
            return null;
        }

        public function getOldSchemaState(string $statePath): mixed
        {
            return null;
        }

        public function getSchemaComponent(string $key, bool $withHidden = false, ?Component $skipComponentChildContainersWhileSearching = null): Component | Action | ActionGroup | null
        {
            return null;
        }

        public function getSchema(string $name): ?Schema
        {
            return null;
        }

        public function currentlyValidatingSchema(?Schema $schema): void
        {
            // No-op for testing harness.
        }

        public function getDefaultTestingSchemaName(): ?string
        {
            return null;
        }
    })
        ->components([$component])
        ->getComponents();
}
