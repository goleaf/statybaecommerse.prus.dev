<?php

declare(strict_types=1);

use App\Filament\Resources\VariantCombinationResource;
use App\Filament\Resources\VariantCombinationResource\Pages\CreateVariantCombination;
use App\Filament\Resources\VariantCombinationResource\Pages\EditVariantCombination;
use App\Filament\Resources\VariantCombinationResource\Pages\ListVariantCombinations;
use App\Filament\Resources\VariantCombinationResource\Pages\ViewVariantCombination;
use App\Models\Product;
use App\Models\User;
use App\Models\VariantCombination;
use App\Support\Nav;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;

// Note: we avoid RefreshDatabase here to use the shared TestingDatabase harness.

// Import Pest Livewire helper for component testing
use function Pest\Livewire\livewire;

beforeEach(function () {
    // Ensure a single reusable admin user to avoid unique email collisions
    $this->adminUser = User::updateOrCreate(
        ['email' => 'admin@example.com'],
        [
            'is_admin' => true,
            'name'     => 'Admin User',
            // Provide a default password to satisfy fillable constraints
            'password' => bcrypt('password'),
        ],
    );

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

describe('VariantCombinationResource', function () {
    it('feature: can render the list page', function () {
        $this->actingAs($this->adminUser);

        livewire(ListVariantCombinations::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$this->variantCombination]);
    });

    it('feature: can render the create page', function () {
        $this->actingAs($this->adminUser);

        livewire(CreateVariantCombination::class)
            ->assertOk();
    });

    it('feature: can render the view page', function () {
        $this->actingAs($this->adminUser);

        livewire(ViewVariantCombination::class, [
            'record' => $this->variantCombination->id,
        ])
            ->assertOk();
    });

    it('feature: can render the edit page', function () {
        $this->actingAs($this->adminUser);

        livewire(EditVariantCombination::class, [
            'record' => $this->variantCombination->id,
        ])
            ->assertOk();
    });

    it('feature: can create a new variant combination', function () {
        $this->actingAs($this->adminUser);

        $newCombinationData = [
            'product_id' => $this->product->id,
            'attribute_combinations' => [
                'color' => 'blue',
                'size' => 'medium',
            ],
            'is_available' => true,
        ];

        livewire(CreateVariantCombination::class)
            ->fillForm($newCombinationData)
            ->call('create')
            ->assertNotified();

        $this->assertDatabaseHas('variant_combinations', [
            'product_id' => $this->product->id,
            'is_available' => true,
        ]);
    });

    it('feature: can update a variant combination', function () {
        $this->actingAs($this->adminUser);

        $updatedData = [
            'is_available' => false,
            'attribute_combinations' => [
                'color' => 'green',
                'size' => 'small',
            ],
        ];

        livewire(EditVariantCombination::class, [
            'record' => $this->variantCombination->id,
        ])
            ->fillForm($updatedData)
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas('variant_combinations', [
            'id' => $this->variantCombination->id,
            'is_available' => false,
        ]);
    });

    it('feature: can delete a variant combination', function () {
        $this->actingAs($this->adminUser);

        livewire(EditVariantCombination::class, [
            'record' => $this->variantCombination->id,
        ])
            ->callAction('delete')
            ->assertNotified();

        $this->assertSoftDeleted('variant_combinations', [
            'id' => $this->variantCombination->id,
        ]);
    });

    it('feature: can toggle availability of a variant combination', function () {
        $this->actingAs($this->adminUser);

        livewire(ListVariantCombinations::class)
            ->callTableAction('toggle_availability', $this->variantCombination)
            ->assertNotified();

        $this->variantCombination->refresh();
        expect($this->variantCombination->is_available)->toBeFalse();
    });

    it('feature: can duplicate a variant combination', function () {
        $this->actingAs($this->adminUser);

        livewire(ListVariantCombinations::class)
            ->callTableAction('duplicate', $this->variantCombination)
            ->assertNotified();

        $this->assertDatabaseCount('variant_combinations', 2);
    });

    it('feature: can validate a variant combination', function () {
        $this->actingAs($this->adminUser);

        livewire(ListVariantCombinations::class)
            ->callTableAction('validate_combination', $this->variantCombination)
            ->assertNotified();
    });

    it('feature: can perform bulk actions', function () {
        $this->actingAs($this->adminUser);

        $secondCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'is_available' => false,
        ]);

        livewire(ListVariantCombinations::class)
            ->callTableBulkAction('make_available', [$this->variantCombination, $secondCombination])
            ->assertNotified();

        $this->variantCombination->refresh();
        $secondCombination->refresh();

        expect($this->variantCombination->is_available)->toBeTrue();
        expect($secondCombination->is_available)->toBeTrue();
    });

    it('feature: can filter by product', function () {
        $this->actingAs($this->adminUser);

        $anotherProduct = Product::factory()->create();
        $anotherCombination = VariantCombination::factory()->create([
            'product_id' => $anotherProduct->id,
        ]);

        livewire(ListVariantCombinations::class)
            ->filterTable('product_id', $this->product->id)
            ->assertCanSeeTableRecords([$this->variantCombination])
            ->assertCanNotSeeTableRecords([$anotherCombination]);
    });

    it('feature: can filter by availability', function () {
        $this->actingAs($this->adminUser);

        $unavailableCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'is_available' => false,
        ]);

        livewire(ListVariantCombinations::class)
            ->filterTable('is_available', true)
            ->assertCanSeeTableRecords([$this->variantCombination])
            ->assertCanNotSeeTableRecords([$unavailableCombination]);
    });

    it('feature: can search variant combinations', function () {
        $this->actingAs($this->adminUser);

        livewire(ListVariantCombinations::class)
            ->searchTable('red')
            ->assertCanSeeTableRecords([$this->variantCombination]);
    });

    it('feature: can sort variant combinations', function () {
        $this->actingAs($this->adminUser);

        $olderCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'created_at' => now()->subDays(1),
        ]);

        livewire(ListVariantCombinations::class)
            ->sortTable('created_at', 'asc')
            ->assertCanSeeTableRecordsInOrder([$olderCombination, $this->variantCombination]);
    });

    it('feature: can generate combinations via header action', function () {
        $this->actingAs($this->adminUser);

        livewire(ListVariantCombinations::class)
            ->callTableHeaderAction('generate_combinations')
            ->assertNotified();
    });

    it('feature: can validate selected combinations via bulk action', function () {
        $this->actingAs($this->adminUser);

        $invalidCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'attribute_combinations' => [],
        ]);

        livewire(ListVariantCombinations::class)
            ->callTableBulkAction('validate_selected', [$this->variantCombination, $invalidCombination])
            ->assertNotified();
    });

    it('feature: can duplicate selected combinations via bulk action', function () {
        $this->actingAs($this->adminUser);

        $secondCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
        ]);

        livewire(ListVariantCombinations::class)
            ->callTableBulkAction('duplicate_selected', [$this->variantCombination, $secondCombination])
            ->assertNotified();

        $this->assertDatabaseCount('variant_combinations', 4); // 2 original + 2 duplicated
    });

    it('feature: can delete selected combinations via bulk action', function () {
        $this->actingAs($this->adminUser);

        $secondCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
        ]);

        livewire(ListVariantCombinations::class)
            ->callTableBulkAction('delete', [$this->variantCombination, $secondCombination])
            ->assertNotified();

        $this->assertSoftDeleted('variant_combinations', [
            'id' => $this->variantCombination->id,
        ]);

        $this->assertSoftDeleted('variant_combinations', [
            'id' => $secondCombination->id,
        ]);
    });

    it('feature: shows correct navigation labels', function () {
        expect(VariantCombinationResource::getNavigationLabel())->toBe('admin.variant_combinations.navigation_label');
        expect(VariantCombinationResource::getPluralModelLabel())->toBe('admin.variant_combinations.plural_model_label');
        expect(VariantCombinationResource::getModelLabel())->toBe('admin.variant_combinations.model_label');
    });

    it('feature: has correct navigation configuration', function () {
        expect(VariantCombinationResource::getNavigationIcon())->toBe(
            Nav::iconForResource(VariantCombinationResource::class)
        );
        expect(VariantCombinationResource::getNavigationGroup())->toBe(
            Nav::groupForResource(VariantCombinationResource::class)
        );
        expect(VariantCombinationResource::getNavigationSort())->toBe(
            Nav::sortForResource(VariantCombinationResource::class)
        );
    });

    it('feature: has correct model configuration', function () {
        expect(VariantCombinationResource::getModel())->toBe(VariantCombination::class);
    });

    it('feature: has correct pages configuration', function () {
        $pages = VariantCombinationResource::getPages();

        expect($pages)->toHaveKey('index');
        expect($pages)->toHaveKey('create');
        expect($pages)->toHaveKey('view');
        expect($pages)->toHaveKey('edit');
    });

    it('feature: has correct relations configuration', function () {
        $relations = VariantCombinationResource::getRelations();
        expect($relations)->toBeArray();
    });
});

describe('VariantCombinationResource Form', function () {
    it('feature: has correct form schema', function () {
        $form = VariantCombinationResource::form(Schema::make());

        expect($form->getComponents())->toHaveCount(3); // 3 sections

        // Check if sections exist
        $schema = $form->getComponents();
        $sectionLabels = collect($schema)->map(fn ($component) => $component->getLabel());

        expect($sectionLabels)->toContain('admin.variant_combinations.basic_information');
        expect($sectionLabels)->toContain('admin.variant_combinations.attribute_combinations');
        expect($sectionLabels)->toContain('admin.variant_combinations.additional_information');
    });

    it('feature: has product selection field', function () {
        $form = VariantCombinationResource::form(Schema::make());
        $schema = $form->getComponents();

        $basicInfoSection = $schema[0];
        $grid = $basicInfoSection->getChildComponents()[0];
        $productField = $grid->getChildComponents()[0];

        expect($productField)->toBeInstanceOf(Select::class);
        expect($productField->getName())->toBe('product_id');
    });

    it('feature: has availability toggle field', function () {
        $form = VariantCombinationResource::form(Schema::make());
        $schema = $form->getComponents();

        $basicInfoSection = $schema[0];
        $grid = $basicInfoSection->getChildComponents()[0];
        $toggleField = $grid->getChildComponents()[1];

        expect($toggleField)->toBeInstanceOf(Toggle::class);
        expect($toggleField->getName())->toBe('is_available');
    });

    it('feature: has attribute combinations field', function () {
        $form = VariantCombinationResource::form(Schema::make());
        $schema = $form->getComponents();

        $combinationsSection = $schema[1];
        $keyValueField = $combinationsSection->getChildComponents()[0];

        expect($keyValueField)->toBeInstanceOf(KeyValue::class);
        expect($keyValueField->getName())->toBe('attribute_combinations');
    });
});

describe('VariantCombinationResource Table', function () {
    it('feature: has correct table columns', function () {
        $table = VariantCombinationResource::table(new \Filament\Tables\Table);
        $columns = $table->getColumns();

        $columnNames = collect($columns)->map(fn ($column) => $column->getName());

        expect($columnNames)->toContain('id');
        expect($columnNames)->toContain('product.name');
        expect($columnNames)->toContain('attribute_combinations');
        expect($columnNames)->toContain('is_available');
        expect($columnNames)->toContain('combination_hash');
        expect($columnNames)->toContain('formatted_combinations');
        expect($columnNames)->toContain('is_valid_combination');
        expect($columnNames)->toContain('created_at');
        expect($columnNames)->toContain('updated_at');
    });

    it('feature: has correct table filters', function () {
        $table = VariantCombinationResource::table(new \Filament\Tables\Table);
        $filters = $table->getFilters();

        $filterNames = collect($filters)->map(fn ($filter) => $filter->getName());

        expect($filterNames)->toContain('product_id');
        expect($filterNames)->toContain('is_available');
        expect($filterNames)->toContain('valid_combinations');
        expect($filterNames)->toContain('recent_combinations');
        expect($filterNames)->toContain('has_attributes');
    });

    it('feature: has correct table actions', function () {
        $table = VariantCombinationResource::table(new \Filament\Tables\Table);
        $actions = $table->getActions();

        $actionNames = collect($actions)->map(fn ($action) => $action->getName());

        expect($actionNames)->toContain('view');
        expect($actionNames)->toContain('edit');
        expect($actionNames)->toContain('toggle_availability');
        expect($actionNames)->toContain('duplicate');
        expect($actionNames)->toContain('validate_combination');
    });

    it('feature: has correct bulk actions', function () {
        $table = VariantCombinationResource::table(new \Filament\Tables\Table);
        $bulkActions = $table->getBulkActions();

        $bulkActionNames = collect($bulkActions)->map(fn ($action) => $action->getName());

        expect($bulkActionNames)->toContain('delete');
        expect($bulkActionNames)->toContain('make_available');
        expect($bulkActionNames)->toContain('make_unavailable');
        expect($bulkActionNames)->toContain('duplicate_selected');
        expect($bulkActionNames)->toContain('validate_selected');
    });

    it('feature: has correct header actions', function () {
        $table = VariantCombinationResource::table(new \Filament\Tables\Table);
        $headerActions = $table->getHeaderActions();

        $headerActionNames = collect($headerActions)->map(fn ($action) => $action->getName());

        expect($headerActionNames)->toContain('generate_combinations');
    });
});
