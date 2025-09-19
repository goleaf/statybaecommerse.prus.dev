<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected SystemSettingCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create test category
        $this->category = SystemSettingCategory::factory()->create([
            'name' => 'Test Category',
            'description' => 'Test category for system settings',
        ]);
    }

    public function test_can_list_system_settings(): void
    {
        SystemSetting::factory()->count(3)->create([
            'category_id' => $this->category->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->assertCanSeeTableRecords(SystemSetting::all());
    }

    public function test_can_create_system_setting(): void
    {
        $data = [
            'key' => 'test_setting',
            'name' => 'Test Setting',
            'description' => 'A test system setting',
            'type' => 'string',
            'value' => 'test_value',
            'category_id' => $this->category->id,
            'is_required' => false,
            'is_public' => false,
            'is_readonly' => false,
            'is_encrypted' => false,
            'cache_ttl' => 3600,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_setting',
            'name' => 'Test Setting',
            'type' => 'string',
            'value' => 'test_value',
        ]);
    }

    public function test_can_edit_system_setting(): void
    {
        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'is_encrypted' => false,  // Ensure not encrypted for test
        ]);

        $data = [
            'name' => 'Updated Setting Name',
            'value' => 'updated_value',
            'type' => 'string',
            'cache_ttl' => 3600,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\EditSystem::class, [
            'record' => $setting->getKey(),
        ])
            ->fillForm($data)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'id' => $setting->id,
            'name' => 'Updated Setting Name',
        ]);

        // Check the value through the model (which handles encryption)
        $updatedSetting = SystemSetting::find($setting->id);
        $this->assertEquals('updated_value', $updatedSetting->value);
    }

    public function test_can_view_system_setting(): void
    {
        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ViewSystem::class, [
            'record' => $setting->getKey(),
        ])
            ->assertOk();
    }

    public function test_can_filter_system_settings_by_category(): void
    {
        $category1 = SystemSettingCategory::factory()->create(['name' => 'Category 1']);
        $category2 = SystemSettingCategory::factory()->create(['name' => 'Category 2']);

        $setting1 = SystemSetting::factory()->create(['category_id' => $category1->id]);
        $setting2 = SystemSetting::factory()->create(['category_id' => $category2->id]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->filterTable('category', $category1->id)
            ->assertCanSeeTableRecords([$setting1])
            ->assertCanNotSeeTableRecords([$setting2]);
    }

    public function test_can_filter_system_settings_by_type(): void
    {
        $stringSetting = SystemSetting::factory()->create(['type' => 'string']);
        $booleanSetting = SystemSetting::factory()->create(['type' => 'boolean']);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->filterTable('type', 'string')
            ->assertCanSeeTableRecords([$stringSetting])
            ->assertCanNotSeeTableRecords([$booleanSetting]);
    }

    public function test_can_filter_required_settings(): void
    {
        $requiredSetting = SystemSetting::factory()->create(['is_required' => true]);
        $optionalSetting = SystemSetting::factory()->create(['is_required' => false]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->filterTable('required')
            ->assertCanSeeTableRecords([$requiredSetting])
            ->assertCanNotSeeTableRecords([$optionalSetting]);
    }

    public function test_can_filter_public_settings(): void
    {
        $publicSetting = SystemSetting::factory()->create(['is_public' => true]);
        $privateSetting = SystemSetting::factory()->create(['is_public' => false]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->filterTable('public')
            ->assertCanSeeTableRecords([$publicSetting])
            ->assertCanNotSeeTableRecords([$privateSetting]);
    }

    public function test_can_search_system_settings(): void
    {
        $setting1 = SystemSetting::factory()->create(['name' => 'Test Setting 1']);
        $setting2 = SystemSetting::factory()->create(['name' => 'Another Setting']);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->searchTable('Test Setting')
            ->assertOk();
    }

    public function test_system_setting_validation(): void
    {
        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm([
                'key' => '',  // Required field
                'name' => '',  // Required field
                'type' => '',  // Required field
            ])
            ->call('create')
            ->assertHasFormErrors(['key', 'name', 'type']);
    }

    public function test_system_setting_unique_key(): void
    {
        $existingSetting = SystemSetting::factory()->create(['key' => 'existing_key']);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm([
                'key' => 'existing_key',  // Duplicate key
                'name' => 'Test Setting',
                'type' => 'string',
                'value' => 'test_value',
                'category_id' => $this->category->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['key']);
    }

    public function test_system_setting_tabs_functionality(): void
    {
        $generalCategory = SystemSettingCategory::factory()->create(['name' => 'General']);
        $securityCategory = SystemSettingCategory::factory()->create(['name' => 'Security']);

        $generalSetting = SystemSetting::factory()->create(['category_id' => $generalCategory->id]);
        $securitySetting = SystemSetting::factory()->create(['category_id' => $securityCategory->id]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->assertCanSeeTableRecords([$generalSetting, $securitySetting])
            ->assertCanSeeTableRecords([$generalSetting])
            ->assertCanSeeTableRecords([$securitySetting]);
    }

    public function test_can_create_boolean_setting(): void
    {
        $data = [
            'key' => 'test_boolean_setting',
            'name' => 'Test Boolean Setting',
            'description' => 'A test boolean setting',
            'type' => 'boolean',
            'value' => true,
            'category_id' => $this->category->id,
            'cache_ttl' => 3600,
            'is_required' => false,
            'is_public' => false,
            'is_readonly' => false,
            'is_encrypted' => false,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_boolean_setting',
            'type' => 'boolean',
        ]);
    }

    public function test_can_create_json_setting(): void
    {
        $data = [
            'key' => 'test_json_setting',
            'name' => 'Test JSON Setting',
            'description' => 'A test JSON setting',
            'type' => 'json',
            'value' => ['key1' => 'value1', 'key2' => 'value2'],
            'category_id' => $this->category->id,
            'cache_ttl' => 3600,
            'is_required' => false,
            'is_public' => false,
            'is_readonly' => false,
            'is_encrypted' => false,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_json_setting',
            'type' => 'json',
        ]);
    }

    public function test_can_create_encrypted_setting(): void
    {
        $data = [
            'key' => 'test_encrypted_setting',
            'name' => 'Test Encrypted Setting',
            'description' => 'A test encrypted setting',
            'type' => 'string',
            'value' => 'sensitive_data',
            'category_id' => $this->category->id,
            'is_encrypted' => true,
            'cache_ttl' => 3600,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_encrypted_setting',
            'is_encrypted' => true,
        ]);
    }

    public function test_can_create_required_setting(): void
    {
        $data = [
            'key' => 'test_required_setting',
            'name' => 'Test Required Setting',
            'description' => 'A test required setting',
            'type' => 'string',
            'value' => 'required_value',
            'category_id' => $this->category->id,
            'is_required' => true,
            'cache_ttl' => 3600,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_required_setting',
            'is_required' => true,
        ]);
    }

    public function test_can_create_public_setting(): void
    {
        $data = [
            'key' => 'test_public_setting',
            'name' => 'Test Public Setting',
            'description' => 'A test public setting',
            'type' => 'string',
            'value' => 'public_value',
            'category_id' => $this->category->id,
            'is_public' => true,
            'cache_ttl' => 3600,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_public_setting',
            'is_public' => true,
        ]);
    }

    public function test_can_create_readonly_setting(): void
    {
        $data = [
            'key' => 'test_readonly_setting',
            'name' => 'Test Readonly Setting',
            'description' => 'A test readonly setting',
            'type' => 'string',
            'value' => 'readonly_value',
            'category_id' => $this->category->id,
            'is_readonly' => true,
            'cache_ttl' => 3600,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_readonly_setting',
            'is_readonly' => true,
        ]);
    }

    public function test_can_filter_encrypted_settings(): void
    {
        $encryptedSetting = SystemSetting::factory()->create(['is_encrypted' => true]);
        $normalSetting = SystemSetting::factory()->create(['is_encrypted' => false]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->filterTable('encrypted')
            ->assertCanSeeTableRecords([$encryptedSetting])
            ->assertCanNotSeeTableRecords([$normalSetting]);
    }

    public function test_can_filter_readonly_settings(): void
    {
        $readonlySetting = SystemSetting::factory()->create(['is_readonly' => true]);
        $writableSetting = SystemSetting::factory()->create(['is_readonly' => false]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->filterTable('readonly')
            ->assertCanSeeTableRecords([$readonlySetting])
            ->assertCanNotSeeTableRecords([$writableSetting]);
    }

    public function test_system_setting_with_validation_rules(): void
    {
        $data = [
            'key' => 'test_validation_setting',
            'name' => 'Test Validation Setting',
            'description' => 'A test setting with validation',
            'type' => 'string',
            'value' => 'valid_value',
            'category_id' => $this->category->id,
            'validation_rules' => 'required|min:3|max:50',
            'cache_ttl' => 3600,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_validation_setting',
            'validation_rules' => 'required|min:3|max:50',
        ]);
    }

    public function test_system_setting_with_default_value(): void
    {
        $data = [
            'key' => 'test_default_setting',
            'name' => 'Test Default Setting',
            'description' => 'A test setting with default value',
            'type' => 'string',
            'value' => 'current_value',
            'category_id' => $this->category->id,
            'default_value' => 'default_value',
            'cache_ttl' => 3600,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_default_setting',
            'default_value' => 'default_value',
        ]);
    }

    public function test_system_setting_with_options(): void
    {
        $data = [
            'key' => 'test_options_setting',
            'name' => 'Test Options Setting',
            'description' => 'A test setting with options',
            'type' => 'select',
            'value' => 'option1',
            'category_id' => $this->category->id,
            'options' => ['option1' => 'Option 1', 'option2' => 'Option 2'],
            'cache_ttl' => 3600,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_options_setting',
            'type' => 'select',
        ]);
    }

    public function test_system_setting_with_group(): void
    {
        $data = [
            'key' => 'test_group_setting',
            'name' => 'Test Group Setting',
            'description' => 'A test setting with group',
            'type' => 'string',
            'value' => 'group_value',
            'category_id' => $this->category->id,
            'group' => 'test_group',
            'cache_ttl' => 3600,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_group_setting',
            'group' => 'test_group',
        ]);
    }

    public function test_system_setting_with_help_text(): void
    {
        $data = [
            'key' => 'test_help_setting',
            'name' => 'Test Help Setting',
            'description' => 'A test setting with help text',
            'type' => 'string',
            'value' => 'help_value',
            'category_id' => $this->category->id,
            'help_text' => 'This is help text for the setting',
            'cache_ttl' => 3600,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_help_setting',
            'help_text' => 'This is help text for the setting',
        ]);
    }

    public function test_system_setting_with_sort_order(): void
    {
        $data = [
            'key' => 'test_sort_setting',
            'name' => 'Test Sort Setting',
            'description' => 'A test setting with sort order',
            'type' => 'string',
            'value' => 'sort_value',
            'category_id' => $this->category->id,
            'sort_order' => 10,
            'cache_ttl' => 3600,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_sort_setting',
            'sort_order' => 10,
        ]);
    }

    public function test_system_setting_bulk_operations(): void
    {
        $settings = SystemSetting::factory()->count(3)->create([
            'category_id' => $this->category->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->callTableAction('delete', $settings)
            ->assertHasNoTableActionErrors();

        foreach ($settings as $setting) {
            $this->assertSoftDeleted('system_settings', ['id' => $setting->id]);
        }
    }

    public function test_system_setting_export_functionality(): void
    {
        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->callTableAction('export', $setting)
            ->assertHasNoTableActionErrors();
    }

    public function test_system_setting_cache_clear_functionality(): void
    {
        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'cache_key' => 'test_cache_key',
        ]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->callTableAction('clear_cache', $setting)
            ->assertHasNoTableActionErrors();
    }

    public function test_system_setting_health_check(): void
    {
        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->callHeaderAction('system_health')
            ->assertHasNoHeaderActionErrors();
    }

    public function test_system_setting_optimize_system(): void
    {
        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->callHeaderAction('optimize_system')
            ->assertHasNoHeaderActionErrors();
    }

    public function test_system_setting_clear_all_caches(): void
    {
        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->callHeaderAction('clear_all_caches')
            ->assertHasNoHeaderActionErrors();
    }

    public function test_system_setting_stats_widget(): void
    {
        SystemSetting::factory()->count(5)->create([
            'category_id' => $this->category->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemResource\Widgets\SystemSettingStatsWidget::class)
            ->assertOk();
    }

    public function test_system_setting_edit_page_actions(): void
    {
        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'cache_key' => 'test_cache_key',
            'default_value' => 'default_value',
        ]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\EditSystem::class, [
            'record' => $setting->getKey(),
        ])
            ->callHeaderAction('clear_cache')
            ->assertHasNoHeaderActionErrors();
    }

    public function test_system_setting_view_page_actions(): void
    {
        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'cache_key' => 'test_cache_key',
        ]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ViewSystem::class, [
            'record' => $setting->getKey(),
        ])
            ->callHeaderAction('clear_cache')
            ->assertHasNoHeaderActionErrors()
            ->callHeaderAction('refresh_value')
            ->assertHasNoHeaderActionErrors();
    }

    public function test_system_setting_navigation_badge(): void
    {
        SystemSetting::factory()->count(5)->create([
            'category_id' => $this->category->id,
        ]);

        $resource = new \App\Filament\Resources\SystemResource();
        $badge = $resource::getNavigationBadge();

        $this->assertIsString($badge);
        $this->assertEquals('5', $badge);
    }

    public function test_system_setting_navigation_badge_color(): void
    {
        SystemSetting::factory()->count(150)->create([
            'category_id' => $this->category->id,
        ]);

        $resource = new \App\Filament\Resources\SystemResource();
        $color = $resource::getNavigationBadgeColor();

        $this->assertIsString($color);
        $this->assertEquals('success', $color);
    }
}
