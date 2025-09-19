<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SystemSettingsResource;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected SystemSettingCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User'
        ]);

        $this->category = SystemSettingCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category'
        ]);
    }

    public function test_can_list_system_settings(): void
    {
        SystemSetting::factory()->count(3)->create([
            'category_id' => $this->category->id
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->assertCanSeeTableRecords(SystemSetting::all());
    }

    public function test_can_create_system_setting(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->fillForm([
                'key' => 'test_setting',
                'name' => 'Test Setting',
                'description' => 'Test description',
                'type' => 'string',
                'category_id' => $this->category->id,
                'group' => 'test',
                'value' => 'test value',
                'is_public' => true,
                'is_required' => false,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_setting',
            'name' => 'Test Setting',
            'type' => 'string',
            'category_id' => $this->category->id,
        ]);
    }

    public function test_can_view_system_setting(): void
    {
        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ViewSystemSetting::class, [
            'record' => $setting->getKey()
        ])
            ->assertFormSet([
                'key' => $setting->key,
                'name' => $setting->name,
                'type' => $setting->type,
            ]);
    }

    public function test_can_edit_system_setting(): void
    {
        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Original Name'
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\EditSystemSetting::class, [
            'record' => $setting->getKey()
        ])
            ->fillForm([
                'name' => 'Updated Name',
                'description' => 'Updated description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'id' => $setting->id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);
    }

    public function test_can_delete_system_setting(): void
    {
        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\EditSystemSetting::class, [
            'record' => $setting->getKey()
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertSoftDeleted('system_settings', [
            'id' => $setting->id,
        ]);
    }

    public function test_can_filter_by_type(): void
    {
        SystemSetting::factory()->create(['type' => 'string']);
        SystemSetting::factory()->create(['type' => 'boolean']);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->filterTable('type', 'string')
            ->assertCanSeeTableRecords(SystemSetting::where('type', 'string')->get())
            ->assertCanNotSeeTableRecords(SystemSetting::where('type', 'boolean')->get());
    }

    public function test_can_filter_by_category(): void
    {
        $category2 = SystemSettingCategory::factory()->create();

        SystemSetting::factory()->create(['category_id' => $this->category->id]);
        SystemSetting::factory()->create(['category_id' => $category2->id]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->filterTable('category_id', $this->category->id)
            ->assertCanSeeTableRecords(SystemSetting::where('category_id', $this->category->id)->get())
            ->assertCanNotSeeTableRecords(SystemSetting::where('category_id', $category2->id)->get());
    }

    public function test_can_filter_by_public_status(): void
    {
        SystemSetting::factory()->create(['is_public' => true]);
        SystemSetting::factory()->create(['is_public' => false]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->filterTable('is_public', true)
            ->assertCanSeeTableRecords(SystemSetting::where('is_public', true)->get())
            ->assertCanNotSeeTableRecords(SystemSetting::where('is_public', false)->get());
    }

    public function test_can_search_system_settings(): void
    {
        SystemSetting::factory()->create(['name' => 'Test Setting 1']);
        SystemSetting::factory()->create(['name' => 'Test Setting 2']);
        SystemSetting::factory()->create(['name' => 'Different Setting']);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->searchTable('Test')
            ->assertCanSeeTableRecords(SystemSetting::where('name', 'like', '%Test%')->get())
            ->assertCanNotSeeTableRecords(SystemSetting::where('name', 'like', '%Different%')->get());
    }

    public function test_can_reset_setting_to_default(): void
    {
        $setting = SystemSetting::factory()->create([
            'value' => 'current value',
            'default_value' => 'default value',
            'category_id' => $this->category->id
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->callTableAction('reset_to_default', $setting)
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('system_settings', [
            'id' => $setting->id,
            'value' => 'default value',
        ]);
    }

    public function test_can_duplicate_system_setting(): void
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'original_setting',
            'name' => 'Original Setting',
            'category_id' => $this->category->id
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->callTableAction('duplicate', $setting)
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'original_setting_copy',
            'name' => 'Original Setting (Copy)',
        ]);
    }

    public function test_can_export_settings(): void
    {
        SystemSetting::factory()->count(3)->create([
            'category_id' => $this->category->id
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->callTableBulkAction('export_settings', SystemSetting::all())
            ->assertHasNoBulkActionErrors();
    }

    public function test_can_clear_cache(): void
    {
        SystemSetting::factory()->count(3)->create([
            'category_id' => $this->category->id
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->callTableBulkAction('clear_cache', SystemSetting::all())
            ->assertHasNoBulkActionErrors();
    }

    public function test_validation_requires_key(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->fillForm([
                'name' => 'Test Setting',
                'type' => 'string',
                'category_id' => $this->category->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['key' => 'required']);
    }

    public function test_validation_requires_unique_key(): void
    {
        SystemSetting::factory()->create(['key' => 'existing_key']);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->fillForm([
                'key' => 'existing_key',
                'name' => 'Test Setting',
                'type' => 'string',
                'category_id' => $this->category->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['key' => 'unique']);
    }

    public function test_validation_requires_name(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->fillForm([
                'key' => 'test_setting',
                'type' => 'string',
                'category_id' => $this->category->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_validation_requires_type(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->fillForm([
                'key' => 'test_setting',
                'name' => 'Test Setting',
                'category_id' => $this->category->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['type' => 'required']);
    }

    public function test_navigation_group_is_system(): void
    {
        $this->assertEquals(
            NavigationGroup::System->value,
            SystemSettingsResource::getNavigationGroup()
        );
    }

    public function test_navigation_label_is_translated(): void
    {
        $this->assertEquals(
            __('system_settings.title'),
            SystemSettingsResource::getNavigationLabel()
        );
    }

    public function test_model_label_is_translated(): void
    {
        $this->assertEquals(
            __('system_settings.single'),
            SystemSettingsResource::getModelLabel()
        );
    }

    public function test_plural_model_label_is_translated(): void
    {
        $this->assertEquals(
            __('system_settings.plural'),
            SystemSettingsResource::getPluralModelLabel()
        );
    }

    public function test_record_title_attribute_is_key(): void
    {
        $this->assertEquals('key', SystemSettingsResource::getRecordTitleAttribute());
    }

    public function test_navigation_sort_is_one(): void
    {
        $this->assertEquals(1, SystemSettingsResource::getNavigationSort());
    }

    public function test_can_create_category_from_form(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->fillForm([
                'key' => 'test_setting',
                'name' => 'Test Setting',
                'type' => 'string',
                'category_id' => null,
            ])
            ->mountedTableAction('createOption', 'category_id')
            ->fillActionForm([
                'name' => 'New Category',
                'slug' => 'new-category',
                'description' => 'New category description',
            ])
            ->callAction('createOption')
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'name' => 'New Category',
            'slug' => 'new-category',
        ]);
    }

    public function test_form_sections_are_organized(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->assertFormExists()
            ->assertFormFieldExists('key')
            ->assertFormFieldExists('name')
            ->assertFormFieldExists('description')
            ->assertFormFieldExists('type')
            ->assertFormFieldExists('category_id')
            ->assertFormFieldExists('group')
            ->assertFormFieldExists('value')
            ->assertFormFieldExists('is_public')
            ->assertFormFieldExists('is_required')
            ->assertFormFieldExists('is_encrypted')
            ->assertFormFieldExists('is_readonly')
            ->assertFormFieldExists('is_active');
    }

    public function test_table_columns_are_configured(): void
    {
        SystemSetting::factory()->create([
            'category_id' => $this->category->id
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->assertCanSeeTableColumns([
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
            ]);
    }

    public function test_table_filters_are_configured(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->assertCanSeeTableFilters([
                'type',
                'category_id',
                'group',
                'is_public',
                'is_encrypted',
                'is_required',
                'is_active',
            ]);
    }

    public function test_table_actions_are_configured(): void
    {
        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->assertCanSeeTableActions([
                'view',
                'edit',
                'reset_to_default',
                'duplicate',
            ]);
    }

    public function test_bulk_actions_are_configured(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->assertCanSeeBulkActions([
                'delete',
                'export_settings',
                'clear_cache',
            ]);
    }
}
