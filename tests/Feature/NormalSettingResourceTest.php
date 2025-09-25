<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\NormalSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;

final class NormalSettingResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create admin user with proper permissions
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Assign admin role
        $this->admin->assignRole('administrator');
    }

    public function test_can_list_normal_settings(): void
    {
        // Create test data
        NormalSetting::factory()->count(5)->create();

        $this->actingAs($this->admin);

        $response = $this->get('/admin/normal-settings');

        $response->assertStatus(200);
        $response->assertSee('Normal Settings');
    }

    public function test_can_create_normal_setting(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/normal-settings/create');

        $response->assertStatus(200);
        $response->assertSee('Create Normal Setting');
    }

    public function test_can_store_normal_setting(): void
    {
        $this->actingAs($this->admin);

        $settingData = [
            'key' => 'test_setting',
            'value' => 'test_value',
            'description' => 'Test description',
            'type' => 'string',
            'is_public' => true,
            'is_encrypted' => false,
            'is_active' => true,
        ];

        $response = $this->post('/admin/normal-settings', $settingData);

        $this->assertDatabaseHas('enhanced_settings', [
            'key' => 'test_setting',
            'value' => 'test_value',
            'type' => 'string',
        ]);
    }

    public function test_can_view_normal_setting(): void
    {
        $setting = NormalSetting::factory()->create();

        $this->actingAs($this->admin);

        $response = $this->get("/admin/normal-settings/{$setting->id}");

        $response->assertStatus(200);
        $response->assertSee($setting->key);
    }

    public function test_can_edit_normal_setting(): void
    {
        $setting = NormalSetting::factory()->create();

        $this->actingAs($this->admin);

        $response = $this->get("/admin/normal-settings/{$setting->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Normal Setting');
    }

    public function test_can_update_normal_setting(): void
    {
        $setting = NormalSetting::factory()->create();

        $this->actingAs($this->admin);

        $updateData = [
            'key' => 'updated_setting',
            'value' => 'updated_value',
            'description' => 'Updated description',
            'type' => 'integer',
            'is_public' => false,
            'is_encrypted' => true,
            'is_active' => false,
        ];

        $response = $this->put("/admin/normal-settings/{$setting->id}", $updateData);

        $this->assertDatabaseHas('enhanced_settings', [
            'id' => $setting->id,
            'key' => 'updated_setting',
            'value' => 'updated_value',
            'type' => 'integer',
        ]);
    }

    public function test_can_filter_normal_settings_by_type(): void
    {
        NormalSetting::factory()->create(['type' => 'string']);
        NormalSetting::factory()->create(['type' => 'integer']);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/normal-settings?filter[type]=string');

        $response->assertStatus(200);
    }

    public function test_can_filter_public_normal_settings(): void
    {
        NormalSetting::factory()->create(['is_public' => true]);
        NormalSetting::factory()->create(['is_public' => false]);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/normal-settings?filter[is_public]=1');

        $response->assertStatus(200);
    }

    public function test_can_filter_encrypted_normal_settings(): void
    {
        NormalSetting::factory()->create(['is_encrypted' => true]);
        NormalSetting::factory()->create(['is_encrypted' => false]);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/normal-settings?filter[is_encrypted]=1');

        $response->assertStatus(200);
    }

    public function test_can_filter_active_normal_settings(): void
    {
        NormalSetting::factory()->create(['is_active' => true]);
        NormalSetting::factory()->create(['is_active' => false]);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/normal-settings?filter[is_active]=1');

        $response->assertStatus(200);
    }

    public function test_can_search_normal_settings(): void
    {
        NormalSetting::factory()->create(['key' => 'email_setting']);
        NormalSetting::factory()->create(['key' => 'database_setting']);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/normal-settings?search=email');

        $response->assertStatus(200);
    }

    public function test_can_sort_normal_settings_by_key(): void
    {
        NormalSetting::factory()->create(['key' => 'z_setting']);
        NormalSetting::factory()->create(['key' => 'a_setting']);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/normal-settings?sort=key');

        $response->assertStatus(200);
    }

    public function test_can_sort_normal_settings_by_type(): void
    {
        NormalSetting::factory()->create(['type' => 'string']);
        NormalSetting::factory()->create(['type' => 'integer']);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/normal-settings?sort=type');

        $response->assertStatus(200);
    }

    public function test_normal_setting_has_translations(): void
    {
        $setting = NormalSetting::factory()->create();

        $this->assertTrue($setting->translations()->exists() || method_exists($setting, 'translation'));
    }

    public function test_normal_setting_can_get_translated_description(): void
    {
        $setting = NormalSetting::factory()->create([
            'description' => 'Default description',
        ]);

        $this->assertIsString($setting->getTranslatedDescription());
    }

    public function test_normal_setting_can_get_display_name(): void
    {
        $setting = NormalSetting::factory()->create([
            'key' => 'test_key',
        ]);

        $this->assertIsString($setting->getDisplayName());
    }

    public function test_normal_setting_can_get_help_text(): void
    {
        $setting = NormalSetting::factory()->create();

        $this->assertIsString($setting->getHelpText());
    }

    public function test_normal_setting_scope_by_group(): void
    {
        NormalSetting::factory()->create(['group' => 'email']);
        NormalSetting::factory()->create(['group' => 'database']);

        $emailSettings = NormalSetting::byGroup('email')->get();

        $this->assertEquals(1, $emailSettings->count());
        $this->assertEquals('email', $emailSettings->first()->group);
    }

    public function test_normal_setting_scope_public(): void
    {
        NormalSetting::factory()->create(['is_public' => true]);
        NormalSetting::factory()->create(['is_public' => false]);

        $publicSettings = NormalSetting::public()->get();

        $this->assertEquals(1, $publicSettings->count());
        $this->assertTrue($publicSettings->first()->is_public);
    }

    public function test_normal_setting_scope_ordered(): void
    {
        NormalSetting::factory()->create(['group' => 'z_group', 'sort_order' => 2]);
        NormalSetting::factory()->create(['group' => 'a_group', 'sort_order' => 1]);

        $orderedSettings = NormalSetting::ordered()->get();

        $this->assertEquals('a_group', $orderedSettings->first()->group);
        $this->assertEquals(1, $orderedSettings->first()->sort_order);
    }

    public function test_normal_setting_static_get_value(): void
    {
        $setting = NormalSetting::factory()->create([
            'key' => 'test_key',
            'value' => 'test_value',
            'locale' => 'en',
        ]);

        $value = NormalSetting::getValue('test_key', 'default', 'en');

        $this->assertEquals('test_value', $value);
    }

    public function test_normal_setting_static_set_value(): void
    {
        NormalSetting::setValue('new_key', 'new_value', 'general', 'en');

        $this->assertDatabaseHas('enhanced_settings', [
            'key' => 'new_key',
            'value' => 'new_value',
            'group' => 'general',
            'locale' => 'en',
        ]);
    }

    public function test_normal_setting_value_casting(): void
    {
        $setting = NormalSetting::factory()->create([
            'type' => 'boolean',
            'value' => '1',
        ]);

        $this->assertIsBool($setting->value);
        $this->assertTrue($setting->value);
    }

    public function test_normal_setting_json_value_casting(): void
    {
        $setting = NormalSetting::factory()->create([
            'type' => 'json',
            'value' => json_encode(['key' => 'value']),
        ]);

        $this->assertIsArray($setting->value);
        $this->assertEquals('value', $setting->value['key']);
    }

    public function test_normal_setting_encrypted_value(): void
    {
        $setting = NormalSetting::factory()->create([
            'is_encrypted' => true,
            'value' => 'sensitive_data',
        ]);

        // The value should be encrypted in the database
        $this->assertNotEquals('sensitive_data', $setting->getRawOriginal('value'));

        // But should be decrypted when accessed
        $this->assertEquals('sensitive_data', $setting->value);
    }

    public function test_normal_setting_has_fillable_attributes(): void
    {
        $setting = NormalSetting::factory()->create([
            'group' => 'test',
            'key' => 'test_key',
            'value' => 'test_value',
            'type' => 'string',
            'description' => 'Test description',
            'is_public' => true,
            'is_encrypted' => false,
            'sort_order' => 1,
        ]);

        $this->assertEquals('test', $setting->group);
        $this->assertEquals('test_key', $setting->key);
        $this->assertEquals('test_value', $setting->value);
        $this->assertEquals('string', $setting->type);
        $this->assertEquals('Test description', $setting->description);
        $this->assertTrue($setting->is_public);
        $this->assertFalse($setting->is_encrypted);
        $this->assertEquals(1, $setting->sort_order);
    }

    public function test_normal_setting_has_casts(): void
    {
        $setting = NormalSetting::factory()->create([
            'is_public' => '1',
            'is_encrypted' => '0',
            'sort_order' => '5',
        ]);

        $this->assertIsBool($setting->is_public);
        $this->assertIsBool($setting->is_encrypted);
        $this->assertIsInt($setting->sort_order);
    }

    public function test_normal_setting_validation_rules_casting(): void
    {
        $setting = NormalSetting::factory()->create([
            'validation_rules' => json_encode(['required', 'string']),
        ]);

        $this->assertIsArray($setting->validation_rules);
        $this->assertContains('required', $setting->validation_rules);
        $this->assertContains('string', $setting->validation_rules);
    }

    public function test_normal_setting_scope_for_locale(): void
    {
        NormalSetting::factory()->create(['locale' => 'en']);
        NormalSetting::factory()->create(['locale' => 'lt']);

        $enSettings = NormalSetting::forLocale('en')->get();

        $this->assertEquals(1, $enSettings->count());
        $this->assertEquals('en', $enSettings->first()->locale);
    }

    public function test_normal_setting_booted_encryption(): void
    {
        $setting = new NormalSetting([
            'key' => 'test_key',
            'value' => 'sensitive_data',
            'is_encrypted' => true,
            'type' => 'string',
        ]);

        $setting->save();

        // The value should be encrypted in the database
        $this->assertNotEquals('sensitive_data', $setting->getRawOriginal('value'));

        // But should be decrypted when accessed
        $this->assertEquals('sensitive_data', $setting->value);
    }
}
