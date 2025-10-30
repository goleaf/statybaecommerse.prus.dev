<?php

declare(strict_types=1);

namespace Tests\Feature;

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
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Feature coverage for the Filament variant combination resource.
 */
final class VariantCombinationResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authenticated administrator reused across scenarios to avoid duplicate factories.
     */
    private User $adminUser;

    /**
     * Catalogue product used for associating variant combinations in tests.
     */
    private Product $product;

    /**
     * Baseline variant combination created for shared assertions.
     */
    private VariantCombination $variantCombination;

    protected function setUp(): void
    {
        parent::setUp();

        // Authenticate a deterministic admin user so Filament policies allow access in every test.
        $this->adminUser = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Create a product to ensure combination relationships resolve cleanly.
        $this->product = Product::factory()->create([
            'is_enabled' => true,
        ]);

        // Seed a reusable variant combination record for list/detail interactions.
        $this->variantCombination = VariantCombination::factory()->create([
            'product_id'             => $this->product->id,
            'attribute_combinations' => [
                'color' => 'red',
                'size'  => 'large',
            ],
            'is_available' => true,
        ]);

        // Ensure all Livewire components execute as the administrator by default.
        $this->actingAs($this->adminUser);
    }

    public function test_list_page_can_render_records(): void
    {
        // Assert the listing Livewire component renders and exposes the seeded combination.
        Livewire::test(ListVariantCombinations::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$this->variantCombination]);
    }

    public function test_create_page_can_render(): void
    {
        // Confirm the create page boots successfully for the authenticated admin.
        Livewire::test(CreateVariantCombination::class)
            ->assertOk();
    }

    public function test_view_page_can_render(): void
    {
        // Validate the detail page loads the requested record without errors.
        Livewire::test(ViewVariantCombination::class, ['record' => $this->variantCombination->id])
            ->assertOk();
    }

    public function test_edit_page_can_render(): void
    {
        // Verify the edit component mounts with the seeded record available.
        Livewire::test(EditVariantCombination::class, ['record' => $this->variantCombination->id])
            ->assertOk();
    }

    public function test_can_create_variant_combination(): void
    {
        // Assemble a payload for a new variant combination associated with the shared product.
        $newCombinationData = [
            'product_id'             => $this->product->id,
            'attribute_combinations' => [
                'color' => 'blue',
                'size'  => 'medium',
            ],
            'is_available' => true,
        ];

        // Submit the form and expect Filament to dispatch a success notification.
        Livewire::test(CreateVariantCombination::class)
            ->fillForm($newCombinationData)
            ->call('create')
            ->assertNotified();

        // Confirm the database contains the new combination with the supplied attributes.
        $this->assertDatabaseHas('variant_combinations', [
            'product_id'   => $this->product->id,
            'is_available' => true,
        ]);
    }

    public function test_can_update_variant_combination(): void
    {
        // Update the seeded combination with new availability and attribute data.
        $updatedData = [
            'is_available'           => false,
            'attribute_combinations' => [
                'color' => 'green',
                'size'  => 'small',
            ],
        ];

        // Save the changes through the edit page and expect success feedback.
        Livewire::test(EditVariantCombination::class, ['record' => $this->variantCombination->id])
            ->fillForm($updatedData)
            ->call('save')
            ->assertNotified();

        // Reload the record to assert the persisted changes match expectations.
        $this->variantCombination->refresh();
        $this->assertFalse($this->variantCombination->is_available);
        $this->assertSame('green', $this->variantCombination->attribute_combinations['color']);
        $this->assertSame('small', $this->variantCombination->attribute_combinations['size']);
    }

    public function test_can_delete_variant_combination(): void
    {
        // Trigger the delete action from the edit page to exercise the soft-delete workflow.
        Livewire::test(EditVariantCombination::class, ['record' => $this->variantCombination->id])
            ->callAction('delete')
            ->assertNotified();

        // Soft deletion should retain the record with a non-null deleted_at timestamp.
        $this->assertSoftDeleted('variant_combinations', [
            'id' => $this->variantCombination->id,
        ]);
    }

    public function test_can_toggle_availability_from_table_action(): void
    {
        // Invoke the table action that flips the availability state for the seeded combination.
        Livewire::test(ListVariantCombinations::class)
            ->callTableAction('toggle_availability', $this->variantCombination)
            ->assertNotified();

        // Refresh the model to assert the availability flag was toggled to false.
        $this->variantCombination->refresh();
        $this->assertFalse($this->variantCombination->is_available);
    }

    public function test_can_duplicate_variant_combination_from_table_action(): void
    {
        // Execute the duplicate table action to replicate the seeded combination.
        Livewire::test(ListVariantCombinations::class)
            ->callTableAction('duplicate', $this->variantCombination)
            ->assertNotified();

        // Ensure exactly one additional record now exists for the same product.
        $this->assertSame(2, VariantCombination::query()->where('product_id', $this->product->id)->count());
    }

    public function test_can_validate_variant_combination_from_table_action(): void
    {
        // Run the validation action to confirm it triggers Filament notifications without errors.
        Livewire::test(ListVariantCombinations::class)
            ->callTableAction('validate_combination', $this->variantCombination)
            ->assertNotified();
    }

    public function test_bulk_action_can_make_combinations_available(): void
    {
        // Create an additional unavailable combination to verify the bulk action updates multiple records.
        $secondCombination = VariantCombination::factory()->create([
            'product_id'   => $this->product->id,
            'is_available' => false,
        ]);

        // Run the make_available bulk action and expect a success notification.
        Livewire::test(ListVariantCombinations::class)
            ->callTableBulkAction('make_available', [$this->variantCombination, $secondCombination])
            ->assertNotified();

        // Refresh both records to confirm their availability flags were updated.
        $this->variantCombination->refresh();
        $secondCombination->refresh();
        $this->assertTrue($this->variantCombination->is_available);
        $this->assertTrue($secondCombination->is_available);
    }

    public function test_table_filter_by_product_limits_results(): void
    {
        // Create an extra product and combination to assert the filter narrows the table results.
        $anotherProduct = Product::factory()->create();
        $anotherCombination = VariantCombination::factory()->create([
            'product_id' => $anotherProduct->id,
        ]);

        // Apply the product filter and ensure only the targeted combination remains visible.
        Livewire::test(ListVariantCombinations::class)
            ->filterTable('product_id', $this->product->id)
            ->assertCanSeeTableRecords([$this->variantCombination])
            ->assertCanNotSeeTableRecords([$anotherCombination]);
    }

    public function test_table_filter_by_availability_limits_results(): void
    {
        // Provision an unavailable combination for the same product to validate the availability filter.
        $unavailableCombination = VariantCombination::factory()->create([
            'product_id'   => $this->product->id,
            'is_available' => false,
        ]);

        // Filter for available combinations and confirm the unavailable record is excluded.
        Livewire::test(ListVariantCombinations::class)
            ->filterTable('is_available', true)
            ->assertCanSeeTableRecords([$this->variantCombination])
            ->assertCanNotSeeTableRecords([$unavailableCombination]);
    }

    public function test_table_search_finds_matching_combinations(): void
    {
        // Search by a keyword present in the seeded combination attributes and expect it to remain visible.
        Livewire::test(ListVariantCombinations::class)
            ->searchTable('red')
            ->assertCanSeeTableRecords([$this->variantCombination]);
    }

    public function test_table_sorting_orders_by_created_at(): void
    {
        // Create an older combination to confirm ascending order lists it before the newer record.
        $olderCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
            'created_at' => now()->subDay(),
        ]);

        // Retrieve the created_at column configuration to ensure it is marked sortable.
        $createdAtColumn = Collection::make(VariantCombinationResource::tableColumns())
            ->first(static fn (TextColumn|BadgeColumn|IconColumn $column) => $column->getName() === 'created_at');
        $this->assertNotNull($createdAtColumn);
        $this->assertTrue($createdAtColumn->isSortable());

        // Query using the resource's base query to validate the ordering behaviour explicitly.
        $sortedIds = VariantCombinationResource::getEloquentQuery()
            ->whereIn('id', [$olderCombination->id, $this->variantCombination->id])
            ->orderBy('created_at', 'asc')
            ->pluck('id')
            ->all();

        $this->assertSame([$olderCombination->id, $this->variantCombination->id], $sortedIds);
    }

    public function test_header_action_can_generate_combinations(): void
    {
        // Execute the custom header action to ensure it emits the expected notification.
        Livewire::test(ListVariantCombinations::class)
            ->callTableAction('generate_combinations')
            ->assertNotified();
    }

    public function test_bulk_action_can_validate_selected_combinations(): void
    {
        // Add an invalid combination lacking attributes to confirm the validation bulk action still completes.
        $invalidCombination = VariantCombination::factory()->create([
            'product_id'             => $this->product->id,
            'attribute_combinations' => [],
        ]);

        // Run the validation bulk action and verify that a notification is dispatched.
        Livewire::test(ListVariantCombinations::class)
            ->callTableBulkAction('validate_selected', [$this->variantCombination, $invalidCombination])
            ->assertNotified();
    }

    public function test_bulk_action_can_duplicate_selected_combinations(): void
    {
        // Seed an additional combination to ensure duplication handles multiple records.
        $secondCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
        ]);

        // Trigger the duplicate bulk action and confirm a success notification occurs.
        Livewire::test(ListVariantCombinations::class)
            ->callTableBulkAction('duplicate_selected', [$this->variantCombination, $secondCombination])
            ->assertNotified();

        // Two duplicates should be created in addition to the originals.
        $this->assertSame(4, VariantCombination::query()->where('product_id', $this->product->id)->count());
    }

    public function test_bulk_action_can_delete_selected_combinations(): void
    {
        // Create another combination that will be deleted in bulk with the original record.
        $secondCombination = VariantCombination::factory()->create([
            'product_id' => $this->product->id,
        ]);

        // Execute the delete bulk action and ensure Filament surfaces a notification.
        Livewire::test(ListVariantCombinations::class)
            ->callTableBulkAction('delete', [$this->variantCombination, $secondCombination])
            ->assertNotified();

        // Both records should be soft deleted after the action completes.
        $this->assertSoftDeleted('variant_combinations', ['id' => $this->variantCombination->id]);
        $this->assertSoftDeleted('variant_combinations', ['id' => $secondCombination->id]);
    }

    public function test_resource_exposes_expected_navigation_labels(): void
    {
        // Assert the resource returns the translation keys used for navigation labelling.
        $this->assertSame('admin.variant_combinations.navigation_label', VariantCombinationResource::getNavigationLabel());
        $this->assertSame('admin.variant_combinations.plural_model_label', VariantCombinationResource::getPluralModelLabel());
        $this->assertSame('admin.variant_combinations.model_label', VariantCombinationResource::getModelLabel());
    }

    public function test_resource_navigation_configuration_matches_nav_support(): void
    {
        // Validate navigation metadata delegates to the shared Nav helper for consistency.
        $this->assertSame(
            Nav::iconForResource(VariantCombinationResource::class),
            VariantCombinationResource::getNavigationIcon(),
        );
        $this->assertSame(
            Nav::groupForResource(VariantCombinationResource::class),
            VariantCombinationResource::getNavigationGroup(),
        );
        $this->assertSame(
            Nav::sortForResource(VariantCombinationResource::class),
            VariantCombinationResource::getNavigationSort(),
        );
    }

    public function test_resource_model_configuration_is_correct(): void
    {
        // Confirm the resource references the VariantCombination model class.
        $this->assertSame(VariantCombination::class, VariantCombinationResource::getModel());
    }

    public function test_resource_pages_configuration_includes_crud_routes(): void
    {
        // Retrieve the configured pages and ensure the standard CRUD entries exist.
        $pages = VariantCombinationResource::getPages();
        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('view', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    public function test_resource_relations_configuration_is_an_array(): void
    {
        // Relations are currently empty but should still resolve to an array structure.
        $this->assertIsArray(VariantCombinationResource::getRelations());
    }

    public function test_form_schema_contains_expected_sections(): void
    {
        // Inspect the reusable form component graph to verify the section structure.
        $schema = VariantCombinationResource::formComponents();
        $this->assertCount(3, $schema);

        $sectionHeadings = Collection::make($schema)->map(static fn (SchemaSection $component) => $component->getHeading());
        $this->assertContains('admin.variant_combinations.basic_information', $sectionHeadings->all());
        $this->assertContains('admin.variant_combinations.attribute_combinations', $sectionHeadings->all());
        $this->assertContains('admin.variant_combinations.additional_information', $sectionHeadings->all());
    }

    public function test_form_contains_product_select_field(): void
    {
        // Locate the basic information section to inspect its grid layout.
        $schema = VariantCombinationResource::formComponents();
        $basicInfoSection = Collection::make($schema)
            ->first(static fn (SchemaSection $component) => $component->getHeading() === 'admin.variant_combinations.basic_information');
        $this->assertInstanceOf(SchemaSection::class, $basicInfoSection);

        $sectionComponents = $this->normaliseSchemaComponents($basicInfoSection->getDefaultChildComponents());
        $grid = Collection::make($sectionComponents)
            ->first(static fn ($component) => $component instanceof SchemaGrid);
        $this->assertInstanceOf(SchemaGrid::class, $grid);

        $gridComponents = $this->normaliseSchemaComponents($grid->getDefaultChildComponents());
        $productField = Collection::make($gridComponents)
            ->first(static fn ($component) => $component instanceof Select && $component->getName() === 'product_id');
        $this->assertInstanceOf(Select::class, $productField);
    }

    public function test_form_contains_availability_toggle_field(): void
    {
        // Reuse the basic information section to locate the availability toggle component.
        $schema = VariantCombinationResource::formComponents();
        $basicInfoSection = Collection::make($schema)
            ->first(static fn (SchemaSection $component) => $component->getHeading() === 'admin.variant_combinations.basic_information');
        $this->assertInstanceOf(SchemaSection::class, $basicInfoSection);

        $sectionComponents = $this->normaliseSchemaComponents($basicInfoSection->getDefaultChildComponents());
        $grid = Collection::make($sectionComponents)
            ->first(static fn ($component) => $component instanceof SchemaGrid);
        $this->assertInstanceOf(SchemaGrid::class, $grid);

        $gridComponents = $this->normaliseSchemaComponents($grid->getDefaultChildComponents());
        $toggleField = Collection::make($gridComponents)
            ->first(static fn ($component) => $component instanceof Toggle && $component->getName() === 'is_available');
        $this->assertInstanceOf(Toggle::class, $toggleField);
    }

    public function test_form_contains_attribute_combinations_field(): void
    {
        // Inspect the attribute combinations section to confirm the key value component exists.
        $schema = VariantCombinationResource::formComponents();
        $combinationsSection = Collection::make($schema)
            ->first(static fn (SchemaSection $component) => $component->getHeading() === 'admin.variant_combinations.attribute_combinations');
        $this->assertInstanceOf(SchemaSection::class, $combinationsSection);

        $combinationComponents = $this->normaliseSchemaComponents($combinationsSection->getDefaultChildComponents());
        $keyValueField = Collection::make($combinationComponents)
            ->first(static fn ($component) => $component instanceof KeyValue && $component->getName() === 'attribute_combinations');
        $this->assertInstanceOf(KeyValue::class, $keyValueField);

    }

    /**
     * Normalise Filament schema components into a simple array for assertion friendly iteration.
     *
     * @param  array<int, mixed>|Schema  $components
     * @return array<int, mixed>
     */
    private function normaliseSchemaComponents(array|Schema $components): array
    {
        // If Filament wraps components in a Schema container, unwrap the array of child components.
        if ($components instanceof Schema) {
            return $components->getComponents();
        }

        // Already an array so it can be returned as-is for downstream collection usage.
        return $components;
    }

    public function test_table_columns_configuration_matches_expectations(): void
    {
        // Collect column names to ensure all expected entries are defined on the resource.
        $columns = Collection::make(VariantCombinationResource::tableColumns())
            ->map(static fn (TextColumn|BadgeColumn|IconColumn $column) => $column->getName());

        $this->assertContains('id', $columns->all());
        $this->assertContains('product.name', $columns->all());
        $this->assertContains('attribute_combinations', $columns->all());
        $this->assertContains('is_available', $columns->all());
        $this->assertContains('combination_hash', $columns->all());
        $this->assertContains('formatted_combinations', $columns->all());
        $this->assertContains('is_valid_combination', $columns->all());
        $this->assertContains('created_at', $columns->all());
        $this->assertContains('updated_at', $columns->all());
    }

    public function test_table_filters_configuration_matches_expectations(): void
    {
        // Map the configured filters to their names for simple containment assertions.
        $filters = Collection::make(VariantCombinationResource::tableFilters())
            ->map(static fn (Filter|SelectFilter|TernaryFilter $filter) => $filter->getName());

        $this->assertContains('product_id', $filters->all());
        $this->assertContains('is_available', $filters->all());
        $this->assertContains('valid_combinations', $filters->all());
        $this->assertContains('recent_combinations', $filters->all());
        $this->assertContains('has_attributes', $filters->all());
    }

    public function test_table_actions_configuration_matches_expectations(): void
    {
        // Gather action names from the per-record table actions to ensure coverage of custom actions.
        $actions = Collection::make(VariantCombinationResource::tableActions())
            ->map(static fn ($action) => $action->getName());

        $this->assertContains('view', $actions->all());
        $this->assertContains('edit', $actions->all());
        $this->assertContains('toggle_availability', $actions->all());
        $this->assertContains('duplicate', $actions->all());
        $this->assertContains('validate_combination', $actions->all());
    }

    public function test_table_bulk_actions_configuration_matches_expectations(): void
    {
        // Flatten the bulk action groups to inspect the underlying action names.
        $bulkActionNames = Collection::make(VariantCombinationResource::tableBulkActions())
            ->flatMap(static fn ($group) => Collection::make($group->getActions()))
            ->map(static fn ($action) => $action->getName());

        $this->assertContains('delete', $bulkActionNames->all());
        $this->assertContains('make_available', $bulkActionNames->all());
        $this->assertContains('make_unavailable', $bulkActionNames->all());
        $this->assertContains('duplicate_selected', $bulkActionNames->all());
        $this->assertContains('validate_selected', $bulkActionNames->all());
    }

    public function test_table_header_actions_configuration_matches_expectations(): void
    {
        // Convert the header actions to a simple list of names to validate availability of custom triggers.
        $headerActionNames = Collection::make(VariantCombinationResource::tableHeaderActions())
            ->map(static fn ($action) => $action->getName());

        $this->assertContains('generate_combinations', $headerActionNames->all());
    }
}
