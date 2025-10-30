<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\SystemSettingsResource;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\User;
use App\Support\Nav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable as TestableLivewire;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingsResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Keys that should always be present on the System Settings form.
     * The constant keeps the assertion intent centralised so any schema
     * tweaks only need to update this list.
     *
     * @var array<int, string>
     */
    private const FORM_FIELDS = [
        'key',
        'name',
        'description',
        'type',
        'category_id',
        'group',
        'value',
        'is_public',
        'is_required',
        'is_encrypted',
        'is_readonly',
        'is_active',
    ];

    /**
     * Columns that the listing should expose to administrators.
     * Having the identifiers in one place documents expectations for
     * follow-up refactors.
     *
     * @var array<int, string>
     */
    private const TABLE_COLUMNS = [
        'key',
        'name',
        'type',
        'value',
        'category.name',
        'group',
        'is_public',
        'is_encrypted',
        'is_required',
        'is_active',
    ];

    /**
     * Filter handles that must stay available on the resource listing.
     * Centralising the handles ensures assertion intent remains clear.
     *
     * @var array<int, string>
     */
    private const TABLE_FILTERS = [
        'type',
        'category_id',
        'group',
        'is_public',
        'is_encrypted',
        'is_required',
        'is_active',
    ];

    /**
     * Row-level action identifiers that should be rendered for each record.
     * Keeping the expectation centralised helps reviewers when new actions are added.
     *
     * @var array<int, string>
     */
    private const TABLE_ACTIONS = [
        'view',
        'edit',
        'reset_to_default',
        'duplicate',
    ];

    /**
     * Bulk action identifiers that empower administrators from the listing.
     * The list acts as lightweight documentation for future contributors.
     *
     * @var array<int, string>
     */
    private const BULK_ACTIONS = [
        'delete',
        'export_settings',
        'clear_cache',
    ];

    private User $adminUser;

    private SystemSettingCategory $category;

    /**
     * Baseline payload used by form submissions across the suite.
     * @var array<string, mixed>
     */
    private array $baseFormData = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Provision a deterministic administrator that mirrors the Filament guard expectations.
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'name'  => 'Admin User',
        ]);

        // Create the canonical category so downstream factories inherit the relationship effortlessly.
        $this->category = SystemSettingCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        // Seed the default form payload once to avoid repeating literal arrays in each test.
        $this->baseFormData = [
            'key'          => 'test_setting',
            'name'         => 'Test Setting',
            'description'  => 'Test description',
            'type'         => 'string',
            'category_id'  => $this->category->id,
            'group'        => 'general',
            'value'        => 'test value',
            'is_public'    => true,
            'is_required'  => false,
            'is_encrypted' => false,
            'is_readonly'  => false,
            'is_active'    => true,
            'sort_order'   => 1,
        ];
    }

    /**
     * The listing should surface existing settings so administrators can manage them.
     */
    public function test_listing_displays_existing_records(): void
    {
        $settings = SystemSetting::factory()
            ->count(3)
            ->create([
                'category_id' => $this->category->id,
            ]);

        $this->listComponent()
            ->assertCanSeeTableRecords($settings);
    }

    /**
     * Administrators must be able to create a new setting with the configured form schema.
     */
    public function test_can_create_system_setting(): void
    {
        $this->createComponent()
            ->fillForm($this->baseFormData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key'         => $this->baseFormData['key'],
            'name'        => $this->baseFormData['name'],
            'type'        => $this->baseFormData['type'],
            'category_id' => $this->category->id,
        ]);
    }

    /**
     * Viewing a setting should hydrate the infolist with persisted values for quick inspection.
     */
    public function test_can_view_system_setting(): void
    {
        $setting = $this->createSetting();

        $this->viewComponent($setting)
            ->assertFormSet([
                'key'  => $setting->key,
                'name' => $setting->name,
                'type' => $setting->type,
            ]);
    }

    /**
     * Editing a setting must persist the new payload so configuration changes stick.
     */
    public function test_can_edit_system_setting(): void
    {
        $setting = $this->createSetting([
            'name'        => 'Original Name',
            'description' => 'Original description',
        ]);

        $this->editComponent($setting)
            ->fillForm([
                'name'        => 'Updated Name',
                'description' => 'Updated description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'id'          => $setting->id,
            'name'        => 'Updated Name',
            'description' => 'Updated description',
        ]);
    }

    /**
     * Deleting a setting through the table action should soft delete the record.
     */
    public function test_can_delete_system_setting(): void
    {
        $setting = $this->createSetting();

        $this->editComponent($setting)
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertSoftDeleted('system_settings', [
            'id' => $setting->id,
        ]);
    }

    /**
     * Resetting to default should restore the stored value back to its baseline configuration.
     */
    public function test_can_reset_setting_to_default(): void
    {
        $setting = $this->createSetting([
            'value'         => 'current value',
            'default_value' => 'default value',
        ]);

        $this->listComponent()
            ->callTableAction('reset_to_default', $setting)
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('system_settings', [
            'id'    => $setting->id,
            'value' => 'default value',
        ]);
    }

    /**
     * Duplicating a setting should generate a copy with the expected key suffix and label.
     */
    public function test_can_duplicate_system_setting(): void
    {
        $setting = $this->createSetting([
            'key'  => 'original_setting',
            'name' => 'Original Setting',
        ]);

        $this->listComponent()
            ->callTableAction('duplicate', $setting)
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('system_settings', [
            'key'  => 'original_setting_copy',
            'name' => 'Original Setting (Copy)',
        ]);
    }

    /**
     * Exporting from the bulk actions should execute without raising validation issues.
     */
    public function test_can_export_settings(): void
    {
        $settings = SystemSetting::factory()
            ->count(3)
            ->create([
                'category_id' => $this->category->id,
            ]);

        $this->listComponent()
            ->callTableBulkAction('export_settings', $settings)
            ->assertHasNoBulkActionErrors();
    }

    /**
     * Clearing the cache via the bulk action should succeed for existing records.
     */
    public function test_can_clear_cache(): void
    {
        $settings = SystemSetting::factory()
            ->count(3)
            ->create([
                'category_id' => $this->category->id,
            ]);

        $this->listComponent()
            ->callTableBulkAction('clear_cache', $settings)
            ->assertHasNoBulkActionErrors();
    }

    /**
     * The type filter should limit results to only settings matching the selected type.
     */
    public function test_can_filter_by_type(): void
    {
        $visibleSetting = $this->createSetting(['type' => 'string']);
        $hiddenSetting = $this->createSetting(['type' => 'boolean']);

        $this->listComponent()
            ->filterTable('type', 'string')
            ->assertCanSeeTableRecords([$visibleSetting])
            ->assertCanNotSeeTableRecords([$hiddenSetting]);
    }

    /**
     * The category filter should scope records to the chosen taxonomy group.
     */
    public function test_can_filter_by_category(): void
    {
        $otherCategory = SystemSettingCategory::factory()->create();
        $visibleSetting = $this->createSetting(['category_id' => $this->category->id]);
        $hiddenSetting = $this->createSetting([
            'category_id' => $otherCategory->id,
            'key'         => 'other-setting',
        ]);

        $this->listComponent()
            ->filterTable('category_id', $this->category->id)
            ->assertCanSeeTableRecords([$visibleSetting])
            ->assertCanNotSeeTableRecords([$hiddenSetting]);
    }

    /**
     * Public visibility filtering should only display records marked as public.
     */
    public function test_can_filter_by_public_status(): void
    {
        $visibleSetting = $this->createSetting(['is_public' => true, 'key' => 'public_setting']);
        $hiddenSetting = $this->createSetting(['is_public' => false, 'key' => 'private_setting']);

        $this->listComponent()
            ->filterTable('is_public', true)
            ->assertCanSeeTableRecords([$visibleSetting])
            ->assertCanNotSeeTableRecords([$hiddenSetting]);
    }

    /**
     * Search queries must match records by name so administrators can quickly locate settings.
     */
    public function test_can_search_system_settings(): void
    {
        $visibleSetting = $this->createSetting(['name' => 'Test Setting 1', 'key' => 'test_setting_1']);
        $alsoVisible = $this->createSetting(['name' => 'Test Setting 2', 'key' => 'test_setting_2']);
        $hiddenSetting = $this->createSetting(['name' => 'Different Setting', 'key' => 'different_setting']);

        $this->listComponent()
            ->searchTable('Test')
            ->assertCanSeeTableRecords([$visibleSetting, $alsoVisible])
            ->assertCanNotSeeTableRecords([$hiddenSetting]);
    }

    /**
     * The key field is required so settings can be referenced programmatically.
     */
    public function test_validation_requires_key(): void
    {
        $payload = $this->baseFormData;
        unset($payload['key']);

        $this->createComponent()
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['key' => 'required']);
    }

    /**
     * Keys must be unique to prevent collisions when fetching configuration values.
     */
    public function test_validation_requires_unique_key(): void
    {
        $this->createSetting(['key' => 'existing_key']);

        $payload = $this->baseFormData;
        $payload['key'] = 'existing_key';

        $this->createComponent()
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['key' => 'unique']);
    }

    /**
     * A descriptive name is required so administrators can understand the setting.
     */
    public function test_validation_requires_name(): void
    {
        $payload = $this->baseFormData;
        unset($payload['name']);

        $this->createComponent()
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    /**
     * Type selection is required so downstream casting logic behaves correctly.
     */
    public function test_validation_requires_type(): void
    {
        $payload = $this->baseFormData;
        unset($payload['type']);

        $this->createComponent()
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['type' => 'required']);
    }

    /**
     * The navigation group should align with the shared Nav helper for consistency.
     */
    public function test_navigation_group_is_system(): void
    {
        self::assertEquals(
            Nav::groupForResource(SystemSettingsResource::class),
            SystemSettingsResource::getNavigationGroup(),
        );
    }

    /**
     * Navigation labels should return translated strings so the panel respects localisation.
     */
    public function test_navigation_label_is_translated(): void
    {
        self::assertEquals(
            __('system_settings.title'),
            SystemSettingsResource::getNavigationLabel(),
        );
    }

    /**
     * Singular model labels must resolve to the translation to keep UI copy consistent.
     */
    public function test_model_label_is_translated(): void
    {
        self::assertEquals(
            __('system_settings.single'),
            SystemSettingsResource::getModelLabel(),
        );
    }

    /**
     * Plural model labels must also leverage translations for the admin UI.
     */
    public function test_plural_model_label_is_translated(): void
    {
        self::assertEquals(
            __('system_settings.plural'),
            SystemSettingsResource::getPluralModelLabel(),
        );
    }

    /**
     * The record title attribute should remain bound to the key column.
     */
    public function test_record_title_attribute_is_key(): void
    {
        self::assertEquals('key', SystemSettingsResource::getRecordTitleAttribute());
    }

    /**
     * Navigation sort ordering should continue using the shared Nav helper for alignment.
     */
    public function test_navigation_sort_is_one(): void
    {
        self::assertEquals(
            Nav::sortForResource(SystemSettingsResource::class),
            SystemSettingsResource::getNavigationSort(),
        );
    }

    /**
     * Administrators should be able to create a new category inline from the setting form.
     */
    public function test_can_create_category_from_form(): void
    {
        $payload = $this->baseFormData;
        $payload['category_id'] = null;

        $this->createComponent()
            ->fillForm($payload)
            ->mountedTableAction('createOption', 'category_id')
            ->fillActionForm([
                'name'        => 'New Category',
                'slug'        => 'new-category',
                'description' => 'New category description',
            ])
            ->callAction('createOption')
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'name' => 'New Category',
            'slug' => 'new-category',
        ]);
    }

    /**
     * The form should expose every expected field to keep configuration manageable.
     */
    public function test_form_sections_are_organized(): void
    {
        $component = $this->createComponent()
            ->assertFormExists();

        foreach (self::FORM_FIELDS as $field) {
            // Iterate over the required fields so missing schema entries fail fast.
            $component->assertFormFieldExists($field);
        }
    }

    /**
     * The listing should render all configured columns so administrators can audit settings quickly.
     */
    public function test_table_columns_are_configured(): void
    {
        $this->createSetting();

        $component = $this->listComponent();

        // Assert each configured column remains available on the listing.
        $component->assertCanSeeTableColumns(self::TABLE_COLUMNS);
    }

    /**
     * Filters must stay registered to support targeted configuration audits.
     */
    public function test_table_filters_are_configured(): void
    {
        $component = $this->listComponent();

        // Validate each expected filter exists to keep the listing feature-complete.
        $component->assertCanSeeTableFilters(self::TABLE_FILTERS);
    }

    /**
     * Row actions should remain available so administrators can manage settings inline.
     */
    public function test_table_actions_are_configured(): void
    {
        $this->createSetting();

        $component = $this->listComponent();

        // Confirm the presence of each action instead of asserting a brittle ordered list.
        $component->assertCanSeeTableActions(self::TABLE_ACTIONS);
    }

    /**
     * Bulk actions should remain exposed to support mass maintenance flows.
     */
    public function test_bulk_actions_are_configured(): void
    {
        $component = $this->listComponent();

        // Ensures each bulk action identifier remains registered.
        $component->assertCanSeeBulkActions(self::BULK_ACTIONS);
    }

    /**
     * Convenience wrapper that always mounts the listing component as the administrator.
     */
    private function listComponent(): TestableLivewire
    {
        return $this->livewire(SystemSettingsResource\Pages\ListSystemSettings::class);
    }

    /**
     * Helper that mounts the create page while handling authentication.
     */
    private function createComponent(): TestableLivewire
    {
        return $this->livewire(SystemSettingsResource\Pages\CreateSystemSetting::class);
    }

    /**
     * Helper that mounts the edit page for the provided setting.
     */
    private function editComponent(SystemSetting $setting): TestableLivewire
    {
        return $this->livewire(SystemSettingsResource\Pages\EditSystemSetting::class, [
            'record' => $setting->getKey(),
        ]);
    }

    /**
     * Helper that mounts the view page for a specific setting.
     */
    private function viewComponent(SystemSetting $setting): TestableLivewire
    {
        return $this->livewire(SystemSettingsResource\Pages\ViewSystemSetting::class, [
            'record' => $setting->getKey(),
        ]);
    }

    /**
     * Centralised factory helper so all settings share the default category and baseline attributes.
     */
    private function createSetting(array $overrides = []): SystemSetting
    {
        return SystemSetting::factory()->create($overrides + [
            'category_id' => $this->category->id,
        ]);
    }

    /**
     * Proxies Livewire::test while ensuring the admin guard is active.
     */
    private function livewire(string $component, array $parameters = []): TestableLivewire
    {
        return Livewire::actingAs($this->adminUser)->test($component, $parameters);
    }
}

