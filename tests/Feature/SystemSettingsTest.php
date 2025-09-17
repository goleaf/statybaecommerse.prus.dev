<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $regularUser;
    private SystemSettingCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['email' => 'admin@test.com']);
        $this->adminUser->assignRole('admin');

        $this->regularUser = User::factory()->create(['email' => 'user@test.com']);

        $this->category = SystemSettingCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    public function test_admin_can_view_system_settings_index(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('filament.admin.resources.system-settings.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_system_setting(): void
    {
        $this->actingAs($this->adminUser);

        $settingData = [
            'category_id' => $this->category->id,
            'key' => 'test_setting',
            'name' => 'Test Setting',
            'value' => 'test_value',
            'type' => 'string',
            'group' => 'general',
            'description' => 'Test description',
            'is_public' => true,
            'is_required' => false,
            'is_encrypted' => false,
            'is_readonly' => false,
            'is_active' => true,
        ];

        $response = $this->post(route('filament.admin.resources.system-settings.store'), $settingData);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_setting',
            'name' => 'Test Setting',
            'value' => 'test_value',
        ]);
    }

    public function test_admin_can_update_system_setting(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key' => 'test_setting',
            'name' => 'Test Setting',
            'value' => 'old_value',
        ]);

        $updateData = [
            'category_id' => $this->category->id,
            'key' => 'test_setting',
            'name' => 'Updated Setting',
            'value' => 'new_value',
            'type' => 'string',
            'group' => 'general',
            'is_public' => true,
            'is_required' => false,
            'is_encrypted' => false,
            'is_readonly' => false,
            'is_active' => true,
        ];

        $response = $this->put(route('filament.admin.resources.system-settings.update', $setting), $updateData);

        $this->assertDatabaseHas('system_settings', [
            'id' => $setting->id,
            'name' => 'Updated Setting',
            'value' => 'new_value',
        ]);
    }

    public function test_admin_can_delete_system_setting(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key' => 'test_setting',
        ]);

        $response = $this->delete(route('filament.admin.resources.system-settings.destroy', $setting));

        $this->assertSoftDeleted('system_settings', [
            'id' => $setting->id,
        ]);
    }

    public function test_regular_user_cannot_access_system_settings(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('filament.admin.resources.system-settings.index'));

        $response->assertStatus(403);
    }

    public function test_system_setting_key_must_be_unique(): void
    {
        $this->actingAs($this->adminUser);

        SystemSetting::factory()->create([
            'key' => 'duplicate_key',
        ]);

        $settingData = [
            'category_id' => $this->category->id,
            'key' => 'duplicate_key',
            'name' => 'Test Setting',
            'value' => 'test_value',
            'type' => 'string',
            'group' => 'general',
        ];

        $response = $this->post(route('filament.admin.resources.system-settings.store'), $settingData);

        $response->assertSessionHasErrors(['key']);
    }

    public function test_boolean_setting_value_is_cast_correctly(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'type' => 'boolean',
            'value' => 'true',
        ]);

        $this->assertTrue($setting->value);
        $this->assertIsBool($setting->value);
    }

    public function test_number_setting_value_is_cast_correctly(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'type' => 'number',
            'value' => '42',
        ]);

        $this->assertEquals(42, $setting->value);
        $this->assertIsInt($setting->value);
    }

    public function test_array_setting_value_is_cast_correctly(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'type' => 'array',
            'value' => '["item1", "item2"]',
        ]);

        $this->assertEquals(['item1', 'item2'], $setting->value);
        $this->assertIsArray($setting->value);
    }

    public function test_encrypted_setting_is_encrypted_in_database(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'is_encrypted' => true,
            'value' => 'sensitive_data',
        ]);

        $this->assertNotEquals('sensitive_data', $setting->getRawOriginal('value'));
        $this->assertEquals('sensitive_data', $setting->value);
    }

    public function test_public_settings_are_accessible_via_api(): void
    {
        SystemSetting::factory()->create([
            'key' => 'public_setting',
            'value' => 'public_value',
            'is_public' => true,
            'is_active' => true,
        ]);

        SystemSetting::factory()->create([
            'key' => 'private_setting',
            'value' => 'private_value',
            'is_public' => false,
            'is_active' => true,
        ]);

        $response = $this->get(route('frontend.system-settings.api.index'));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'key' => 'public_setting',
            'value' => 'public_value',
        ]);
    }

    public function test_frontend_can_view_system_settings(): void
    {
        SystemSetting::factory()->count(5)->create([
            'is_public' => true,
            'is_active' => true,
            'category_id' => $this->category->id,
        ]);

        $response = $this->get(route('frontend.system-settings.index'));

        $response->assertStatus(200);
        $response->assertSee('System Settings');
    }

    public function test_frontend_can_view_single_system_setting(): void
    {
        $setting = SystemSetting::factory()->create([
            'is_public' => true,
            'is_active' => true,
            'category_id' => $this->category->id,
        ]);

        $response = $this->get(route('frontend.system-settings.show', $setting));

        $response->assertStatus(200);
        $response->assertSee($setting->name);
    }

    public function test_frontend_cannot_view_private_setting(): void
    {
        $setting = SystemSetting::factory()->create([
            'is_public' => false,
            'is_active' => true,
        ]);

        $response = $this->get(route('frontend.system-settings.show', $setting));

        $response->assertStatus(404);
    }

    public function test_frontend_cannot_view_inactive_setting(): void
    {
        $setting = SystemSetting::factory()->create([
            'is_public' => true,
            'is_active' => false,
        ]);

        $response = $this->get(route('frontend.system-settings.show', $setting));

        $response->assertStatus(404);
    }

    public function test_system_setting_can_be_filtered_by_category(): void
    {
        $category2 = SystemSettingCategory::factory()->create([
            'name' => 'Category 2',
            'slug' => 'category-2',
        ]);

        $setting1 = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'is_public' => true,
            'is_active' => true,
        ]);

        $setting2 = SystemSetting::factory()->create([
            'category_id' => $category2->id,
            'is_public' => true,
            'is_active' => true,
        ]);

        $response = $this->get(route('frontend.system-settings.index', ['category' => $this->category->slug]));

        $response->assertStatus(200);
        $response->assertSee($setting1->name);
        $response->assertDontSee($setting2->name);
    }

    public function test_system_setting_can_be_filtered_by_group(): void
    {
        $setting1 = SystemSetting::factory()->create([
            'group' => 'general',
            'is_public' => true,
            'is_active' => true,
        ]);

        $setting2 = SystemSetting::factory()->create([
            'group' => 'ecommerce',
            'is_public' => true,
            'is_active' => true,
        ]);

        $response = $this->get(route('frontend.system-settings.index', ['group' => 'general']));

        $response->assertStatus(200);
        $response->assertSee($setting1->name);
        $response->assertDontSee($setting2->name);
    }

    public function test_system_setting_can_be_searched(): void
    {
        $setting1 = SystemSetting::factory()->create([
            'name' => 'Searchable Setting',
            'is_public' => true,
            'is_active' => true,
        ]);

        $setting2 = SystemSetting::factory()->create([
            'name' => 'Other Setting',
            'is_public' => true,
            'is_active' => true,
        ]);

        $response = $this->get(route('frontend.system-settings.index', ['search' => 'Searchable']));

        $response->assertStatus(200);
        $response->assertSee($setting1->name);
        $response->assertDontSee($setting2->name);
    }

    public function test_api_can_get_setting_by_key(): void
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'api_test_setting',
            'value' => 'api_value',
            'is_public' => true,
            'is_active' => true,
        ]);

        $response = $this->get(route('frontend.system-settings.api.value', 'api_test_setting'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'key' => 'api_test_setting',
                'value' => 'api_value',
            ],
        ]);
    }

    public function test_api_returns_404_for_non_existent_setting(): void
    {
        $response = $this->get(route('frontend.system-settings.api.value', 'non_existent'));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => __('admin.system_settings.setting_not_found'),
        ]);
    }

    public function test_bulk_actions_work_correctly(): void
    {
        $this->actingAs($this->adminUser);

        $settings = SystemSetting::factory()->count(3)->create([
            'is_active' => false,
        ]);

        $response = $this->post(route('filament.admin.resources.system-settings.bulk-activate'), [
            'resources' => $settings->pluck('id')->toArray(),
        ]);

        foreach ($settings as $setting) {
            $this->assertDatabaseHas('system_settings', [
                'id' => $setting->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_system_setting_validation_rules_work(): void
    {
        $this->actingAs($this->adminUser);

        $setting = SystemSetting::factory()->create([
            'validation_rules' => ['min:5', 'max:10'],
        ]);

        $this->assertTrue($setting->validateValue('valid'));
        $this->assertFalse($setting->validateValue('no'));
        $this->assertFalse($setting->validateValue('too_long_value'));
    }

    public function test_system_setting_can_get_formatted_value(): void
    {
        $setting = SystemSetting::factory()->create([
            'type' => 'boolean',
            'value' => 'true',
        ]);

        $formatted = $setting->getFormattedValue();
        $this->assertStringContainsString('Yes', $formatted);
    }

    public function test_system_setting_can_get_icon_for_type(): void
    {
        $setting = SystemSetting::factory()->create(['type' => 'string']);
        
        $icon = $setting->getIconForType();
        $this->assertEquals('heroicon-o-document-text', $icon);
    }

    public function test_system_setting_can_get_color_for_type(): void
    {
        $setting = SystemSetting::factory()->create(['type' => 'number']);
        
        $color = $setting->getColorForType();
        $this->assertEquals('blue', $color);
    }
}


