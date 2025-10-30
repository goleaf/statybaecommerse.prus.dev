<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\SystemResource;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private SystemSettingCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'name'  => 'Admin User',
        ]);

        $this->category = SystemSettingCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    public function test_can_list_system_settings(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'test_setting',
            'name'        => 'Test Setting',
            'type'        => 'string',
            'value'       => 'test value',
        ]);

        Livewire::test(SystemResource\Pages\ListSystems::class)
            ->assertCanSeeTableRecords([$setting]);
    }

    public function test_can_create_system_setting(): void
    {
        $this->actingAs($this->adminUser);

        $settingData = [
            'key'          => 'new_setting',
            'name'         => 'New Setting',
            'description'  => 'A new test setting',
            'type'         => 'string',
            'value'        => 'new value',
            'category_id'  => $this->category->id,
            'group'        => 'test',
            'is_active'    => true,
            'is_public'    => false,
            'is_required'  => false,
            'is_readonly'  => false,
            'is_encrypted' => false,
        ];

        Livewire::test(SystemResource\Pages\CreateSystem::class)
            ->fillForm($settingData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key'  => 'new_setting',
            'name' => 'New Setting',
            'type' => 'string',
        ]);
    }

    public function test_can_edit_system_setting(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'editable_setting',
            'name'        => 'Editable Setting',
            'type'        => 'string',
            'value'       => 'original value',
        ]);

        $updatedData = [
            'name'        => 'Updated Setting Name',
            'value'       => 'updated value',
            'description' => 'Updated description',
        ];

        Livewire::test(SystemResource\Pages\EditSystem::class, ['record' => $setting->id])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors();

        $setting->refresh();
        $this->assertEquals('Updated Setting Name', $setting->name);
        $this->assertEquals('updated value', $setting->value);
    }

    public function test_can_view_system_setting(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'viewable_setting',
            'name'        => 'Viewable Setting',
            'type'        => 'boolean',
            'value'       => true,
        ]);

        Livewire::test(SystemResource\Pages\ViewSystem::class, ['record' => $setting->id])
            ->assertOk();
    }

    public function test_can_delete_system_setting(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'deletable_setting',
        ]);

        Livewire::test(SystemResource\Pages\ListSystems::class)
            ->callTableAction('delete', $setting);

        $this->assertSoftDeleted('system_settings', ['id' => $setting->id]);
    }

    public function test_can_filter_by_category(): void
    {
        $this->actingAs($this->adminUser);

        $category1 = SystemSettingCategory::factory()->create(['name' => 'Category 1']);
        $category2 = SystemSettingCategory::factory()->create(['name' => 'Category 2']);

        $setting1 = SystemSetting::factory()->create(['category_id' => $category1->id]);
        $setting2 = SystemSetting::factory()->create(['category_id' => $category2->id]);

        Livewire::test(SystemResource\Pages\ListSystems::class)
            ->filterTable('category', $category1->id)
            ->assertCanSeeTableRecords([$setting1])
            ->assertCanNotSeeTableRecords([$setting2]);
    }

    public function test_can_filter_by_type(): void
    {
        $this->actingAs($this->adminUser);

        $stringSetting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'type'        => 'string',
        ]);
        $booleanSetting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'type'        => 'boolean',
        ]);

        Livewire::test(SystemResource\Pages\ListSystems::class)
            ->filterTable('type', 'string')
            ->assertCanSeeTableRecords([$stringSetting])
            ->assertCanNotSeeTableRecords([$booleanSetting]);
    }

    public function test_can_filter_by_required_status(): void
    {
        $this->actingAs($this->adminUser);

        $requiredSetting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'is_required' => true,
        ]);
        $optionalSetting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'is_required' => false,
        ]);

        Livewire::test(SystemResource\Pages\ListSystems::class)
            ->filterTable('is_required', true)
            ->assertCanSeeTableRecords([$requiredSetting])
            ->assertCanNotSeeTableRecords([$optionalSetting]);
    }

    public function test_can_filter_by_public_status(): void
    {
        $this->actingAs($this->adminUser);

        $publicSetting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'is_public'   => true,
        ]);
        $privateSetting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'is_public'   => false,
        ]);

        Livewire::test(SystemResource\Pages\ListSystems::class)
            ->filterTable('is_public', true)
            ->assertCanSeeTableRecords([$publicSetting])
            ->assertCanNotSeeTableRecords([$privateSetting]);
    }

    public function test_can_search_system_settings(): void
    {
        $this->actingAs($this->adminUser);

        $searchableSetting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'searchable_key',
            'name'        => 'Searchable Setting',
        ]);
        $otherSetting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'other_key',
            'name'        => 'Other Setting',
        ]);

        Livewire::test(SystemResource\Pages\ListSystems::class)
            ->searchTable('searchable')
            ->assertCanSeeTableRecords([$searchableSetting])
            ->assertCanNotSeeTableRecords([$otherSetting]);
    }

    public function test_can_bulk_delete_system_settings(): void
    {
        $this->actingAs($this->adminUser);

        $setting1 = SystemSetting::factory()->create(['category_id' => $this->category->id]);
        $setting2 = SystemSetting::factory()->create(['category_id' => $this->category->id]);

        Livewire::test(SystemResource\Pages\ListSystems::class)
            ->callTableBulkAction('delete', [$setting1, $setting2]);

        $this->assertSoftDeleted('system_settings', ['id' => $setting1->id]);
        $this->assertSoftDeleted('system_settings', ['id' => $setting2->id]);
    }

    public function test_can_bulk_activate_settings(): void
    {
        $this->actingAs($this->adminUser);

        $setting1 = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'is_active'   => false,
        ]);
        $setting2 = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'is_active'   => false,
        ]);

        Livewire::test(SystemResource\Pages\ListSystems::class)
            ->callTableBulkAction('activate_selected', [$setting1, $setting2]);

        $setting1->refresh();
        $setting2->refresh();
        $this->assertTrue($setting1->is_active);
        $this->assertTrue($setting2->is_active);
    }

    public function test_can_bulk_deactivate_settings(): void
    {
        $this->actingAs($this->adminUser);

        $setting1 = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'is_active'   => true,
        ]);
        $setting2 = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'is_active'   => true,
        ]);

        Livewire::test(SystemResource\Pages\ListSystems::class)
            ->callTableBulkAction('deactivate_selected', [$setting1, $setting2]);

        $setting1->refresh();
        $setting2->refresh();
        $this->assertFalse($setting1->is_active);
        $this->assertFalse($setting2->is_active);
    }

    public function test_can_clear_cache_for_setting(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'cache_key'   => 'test_cache_key',
        ]);

        // Seed the cache so we can assert the action effect clears the key.
        Cache::put('test_cache_key', 'cached-value');
        // Reset any queued notifications to avoid leaking assertions between tests.
        session()->forget('filament.notifications');

        $component = Livewire::actingAs($this->adminUser)
            ->test(SystemResource\Pages\ListSystems::class);

        // Touch the table records so the Filament table actions are available during the test.
        $component->loadTable();

        $component
            // Trigger the cache clearing action for the targeted record.
            ->callTableAction('clear_cache', $setting)
            // Confirm the translated success banner was dispatched.
            ->assertNotified(__('system.cache_cleared'));

        // Ensure the cached value is actually removed after the action runs.
        $this->assertNull(Cache::get('test_cache_key'));
    }

    public function test_can_export_setting(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'exportable_setting',
            'name'        => 'Exportable Setting',
            'value'       => 'export value',
        ]);

        // Freeze time to stabilise the generated download name.
        Carbon::setTestNow('2025-01-01 12:00:00');

        $component = Livewire::actingAs($this->adminUser)
            ->test(SystemResource\Pages\ListSystems::class);

        // Load the table once so the export action is registered for the test harness.
        $component->loadTable();

        $component
            // Invoke the export action to ensure the Livewire bridge executes without validation errors.
            ->callTableAction('export', $setting);

        // Inspect the raw Livewire return payload to confirm the JSON structure matches the record data.
        $livewireReturn = $component->effects['returns'][0] ?? [];
        $this->assertSame([
            'key'      => $setting->key,
            'name'     => $setting->name,
            'value'    => $setting->value,
            'type'     => $setting->type,
            // Livewire serialises the response payload without eager loading the relationship.
            'category' => null,
        ], $livewireReturn['original'] ?? []);

        // Resolve the configured table action directly so we can inspect the full HTTP response object.
        $action = $component->instance()->getTable()->getAction('export');
        $this->assertNotNull($action);
        $action->record($setting);
        $response = $action->call();

        // Validate the real response matches the expected JSON payload and attachment headers.
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame([
            'key'      => $setting->key,
            'name'     => $setting->name,
            'value'    => $setting->value,
            'type'     => $setting->type,
            'category' => $setting->category->name,
        ], $response->getData(true));
        $this->assertSame(
            sprintf('attachment; filename="setting_%s_2025-01-01_12-00-00.json"', $setting->key),
            $response->headers->get('Content-Disposition'),
        );

        // Release the frozen clock so subsequent tests observe real time.
        Carbon::setTestNow();
    }

    public function test_handles_different_setting_types(): void
    {
        $this->actingAs($this->adminUser);

        $types = ['string', 'integer', 'boolean', 'json', 'array', 'file', 'color', 'date', 'datetime', 'email', 'url', 'password'];

        foreach ($types as $type) {
            $setting = SystemSetting::factory()->create([
                'category_id' => $this->category->id,
                'type'        => $type,
                'key'         => "test_{$type}_setting",
                'value'       => match ($type) {
                    'boolean' => true,
                    'integer' => 42,
                    'json'    => '{"test": "value"}',
                    'array'   => '["item1", "item2"]',
                    default   => 'test value',
                },
            ]);

            $this->assertDatabaseHas('system_settings', [
                'type' => $type,
                'key'  => "test_{$type}_setting",
            ]);
        }
    }

    public function test_requires_unique_key(): void
    {
        $this->actingAs($this->adminUser);

        $existingSetting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'unique_key',
        ]);

        $settingData = [
            'key'         => 'unique_key', // Same key
            'name'        => 'Duplicate Key Setting',
            'type'        => 'string',
            'value'       => 'test value',
            'category_id' => $this->category->id,
        ];

        Livewire::test(SystemResource\Pages\CreateSystem::class)
            ->fillForm($settingData)
            ->call('create')
            ->assertHasFormErrors(['key']);
    }

    public function test_validates_required_fields(): void
    {
        $this->actingAs($this->adminUser);

        $settingData = [
            // Missing required fields: key, name, type, category_id
            'value' => 'test value',
        ];

        Livewire::test(SystemResource\Pages\CreateSystem::class)
            ->fillForm($settingData)
            ->call('create')
            ->assertHasFormErrors(['key', 'name', 'type', 'category_id']);
    }

    public function test_can_create_category_through_relationship(): void
    {
        $this->actingAs($this->adminUser);

        $settingData = [
            'key'         => 'setting_with_new_category',
            'name'        => 'Setting with New Category',
            'type'        => 'string',
            'value'       => 'test value',
            'category_id' => [
                'name'        => 'New Category',
                'description' => 'A new category',
                'color'       => '#FF5733',
            ],
        ];

        Livewire::test(SystemResource\Pages\CreateSystem::class)
            ->fillForm($settingData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'name' => 'New Category',
        ]);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'setting_with_new_category',
        ]);
    }

    public function test_can_handle_encrypted_values(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'category_id'  => $this->category->id,
            'key'          => 'encrypted_setting',
            'type'         => 'string',
            'is_encrypted' => true,
        ]);

        // Set the value after creation to trigger encryption
        $setting->value = 'sensitive data';
        $setting->save();

        // The value should be encrypted in the database
        $this->assertNotEquals('sensitive data', $setting->getRawOriginal('value'));

        // But should be decrypted when accessed
        $this->assertEquals('sensitive data', $setting->value);
    }

    public function test_can_handle_json_values(): void
    {
        $this->actingAs($this->adminUser);

        $jsonData = ['key1' => 'value1', 'key2' => 'value2'];

        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'json_setting',
            'type'        => 'json',
            'value'       => $jsonData,
        ]);

        $this->assertEquals($jsonData, $setting->value);
        $this->assertIsArray($setting->value);
    }

    public function test_can_handle_array_values(): void
    {
        $this->actingAs($this->adminUser);

        $arrayData = ['item1', 'item2', 'item3'];

        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'array_setting',
            'type'        => 'array',
            'value'       => $arrayData,
        ]);

        $this->assertEquals($arrayData, $setting->value);
        $this->assertIsArray($setting->value);
    }

    public function test_can_handle_boolean_values(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'boolean_setting',
            'type'        => 'boolean',
            'value'       => true,
        ]);

        $this->assertTrue($setting->value);
        $this->assertIsBool($setting->value);
    }

    public function test_can_handle_integer_values(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'integer_setting',
            'type'        => 'integer',
            'value'       => 42,
        ]);

        $this->assertEquals(42, $setting->value);
        $this->assertIsInt($setting->value);
    }

    public function test_navigation_badge_shows_correct_count(): void
    {
        $this->actingAs($this->adminUser);

        SystemSetting::factory()->count(5)->create(['category_id' => $this->category->id]);

        $badge = SystemResource::getNavigationBadge();
        $this->assertEquals('5', $badge);
    }

    public function test_navigation_badge_color_changes_based_on_count(): void
    {
        $this->actingAs($this->adminUser);

        // Test with low count
        SystemSetting::factory()->count(10)->create(['category_id' => $this->category->id]);
        $color = SystemResource::getNavigationBadgeColor();
        $this->assertEquals('danger', $color);

        // Test with medium count
        SystemSetting::factory()->count(60)->create(['category_id' => $this->category->id]);
        $color = SystemResource::getNavigationBadgeColor();
        $this->assertEquals('warning', $color);

        // Test with high count
        SystemSetting::factory()->count(150)->create(['category_id' => $this->category->id]);
        $color = SystemResource::getNavigationBadgeColor();
        $this->assertEquals('success', $color);
    }
}
