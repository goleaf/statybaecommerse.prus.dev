<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\EnhancedSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EnhancedSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_enhanced_setting_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'group',
            'key',
            'value',
            'type',
            'description',
            'is_public',
            'is_encrypted',
            'validation_rules',
            'sort_order',
        ];

        $setting = new EnhancedSetting();
        $this->assertEquals($expectedFillable, $setting->getFillable());
    }

    public function test_enhanced_setting_has_correct_casts(): void
    {
        $expectedCasts = [
            'id' => 'int',
            'value' => 'json',
            'validation_rules' => 'json',
            'is_public' => 'boolean',
            'is_encrypted' => 'boolean',
            'sort_order' => 'integer',
        ];

        $setting = new EnhancedSetting();
        $casts = $setting->getCasts();

        foreach ($expectedCasts as $attribute => $cast) {
            $this->assertEquals($cast, $casts[$attribute]);
        }
    }

    public function test_enhanced_setting_table_name(): void
    {
        $setting = new EnhancedSetting();
        $this->assertEquals('enhanced_settings', $setting->getTable());
    }

    public function test_enhanced_setting_value_accessor_with_encryption(): void
    {
        $setting = new EnhancedSetting([
            'is_encrypted' => true,
            'value' => encrypt('secret_value'),
        ]);

        // Mock the encrypted value
        $setting->setRawAttributes([
            'is_encrypted' => true,
            'value' => encrypt('secret_value'),
        ]);

        $this->assertEquals('secret_value', $setting->value);
    }

    public function test_enhanced_setting_value_accessor_without_encryption(): void
    {
        $setting = new EnhancedSetting([
            'is_encrypted' => false,
            'value' => 'plain_value',
        ]);

        $this->assertEquals('plain_value', $setting->value);
    }

    public function test_enhanced_setting_value_mutator_with_encryption(): void
    {
        $setting = new EnhancedSetting();
        $setting->is_encrypted = true;
        $setting->value = 'secret_value';

        // The value should be encrypted
        $rawValue = $setting->getAttributes()['value'];
        $this->assertNotEquals('secret_value', $rawValue);

        // But should decrypt correctly
        $this->assertEquals('secret_value', decrypt($rawValue));
    }

    public function test_enhanced_setting_value_mutator_without_encryption(): void
    {
        $setting = new EnhancedSetting();
        $setting->is_encrypted = false;
        $setting->value = 'plain_value';

        $this->assertEquals('plain_value', $setting->getAttributes()['value']);
    }

    public function test_enhanced_setting_by_group_scope(): void
    {
        EnhancedSetting::factory()->create(['group' => 'general']);
        EnhancedSetting::factory()->create(['group' => 'email']);
        EnhancedSetting::factory()->create(['group' => 'general']);

        $generalSettings = EnhancedSetting::byGroup('general')->get();
        $this->assertCount(2, $generalSettings);

        foreach ($generalSettings as $setting) {
            $this->assertEquals('general', $setting->group);
        }
    }

    public function test_enhanced_setting_public_scope(): void
    {
        EnhancedSetting::factory()->create(['is_public' => true]);
        EnhancedSetting::factory()->create(['is_public' => false]);
        EnhancedSetting::factory()->create(['is_public' => true]);

        $publicSettings = EnhancedSetting::public()->get();
        $this->assertCount(2, $publicSettings);

        foreach ($publicSettings as $setting) {
            $this->assertTrue($setting->is_public);
        }
    }

    public function test_enhanced_setting_ordered_scope(): void
    {
        $setting1 = EnhancedSetting::factory()->create([
            'group' => 'general',
            'sort_order' => 3,
            'key' => 'setting_c',
        ]);

        $setting2 = EnhancedSetting::factory()->create([
            'group' => 'general',
            'sort_order' => 1,
            'key' => 'setting_a',
        ]);

        $setting3 = EnhancedSetting::factory()->create([
            'group' => 'email',
            'sort_order' => 2,
            'key' => 'setting_b',
        ]);

        $orderedSettings = EnhancedSetting::ordered()->get();

        // Should be ordered by group, then sort_order, then key
        $this->assertEquals('setting_a', $orderedSettings[0]->key);  // general, 1
        $this->assertEquals('setting_c', $orderedSettings[1]->key);  // general, 3
        $this->assertEquals('setting_b', $orderedSettings[2]->key);  // email, 2
    }

    public function test_enhanced_setting_get_value_static_method(): void
    {
        EnhancedSetting::factory()->create([
            'key' => 'test_key',
            'value' => 'test_value',
        ]);

        $value = EnhancedSetting::getValue('test_key');
        $this->assertEquals('test_value', $value);

        $defaultValue = EnhancedSetting::getValue('non_existent', 'default');
        $this->assertEquals('default', $defaultValue);

        $nullValue = EnhancedSetting::getValue('non_existent');
        $this->assertNull($nullValue);
    }

    public function test_enhanced_setting_set_value_static_method(): void
    {
        // Test creating new setting
        EnhancedSetting::setValue('new_key', 'new_value', 'new_group');

        $this->assertDatabaseHas('enhanced_settings', [
            'key' => 'new_key',
            'value' => 'new_value',
            'group' => 'new_group',
        ]);

        // Test updating existing setting
        EnhancedSetting::setValue('new_key', 'updated_value');

        $this->assertDatabaseHas('enhanced_settings', [
            'key' => 'new_key',
            'value' => 'updated_value',
        ]);

        // Should only have one record
        $this->assertDatabaseCount('enhanced_settings', 1);
    }

    public function test_enhanced_setting_json_value_handling(): void
    {
        $jsonData = [
            'nested' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            'array' => [1, 2, 3, 4, 5],
            'boolean' => true,
            'null' => null,
        ];

        $setting = EnhancedSetting::create([
            'key' => 'json_test',
            'value' => $jsonData,
            'type' => 'json',
        ]);

        // Refresh to get the processed value
        $setting->refresh();

        $this->assertEquals($jsonData, $setting->value);
        $this->assertIsArray($setting->value);
        $this->assertEquals('value1', $setting->value['nested']['key1']);
        $this->assertEquals([1, 2, 3, 4, 5], $setting->value['array']);
        $this->assertTrue($setting->value['boolean']);
        $this->assertNull($setting->value['null']);
    }

    public function test_enhanced_setting_validation_rules_handling(): void
    {
        $rules = [
            'required',
            'string',
            'max:255',
            'unique:users,email',
        ];

        $setting = new EnhancedSetting([
            'key' => 'validation_test',
            'validation_rules' => $rules,
        ]);

        $this->assertEquals($rules, $setting->validation_rules);
        $this->assertIsArray($setting->validation_rules);
        $this->assertContains('required', $setting->validation_rules);
        $this->assertContains('unique:users,email', $setting->validation_rules);
    }

    public function test_enhanced_setting_boolean_values(): void
    {
        $setting = new EnhancedSetting([
            'key' => 'boolean_test',
            'is_public' => true,
            'is_encrypted' => false,
        ]);

        $this->assertTrue($setting->is_public);
        $this->assertFalse($setting->is_encrypted);
        $this->assertIsBool($setting->is_public);
        $this->assertIsBool($setting->is_encrypted);
    }

    public function test_enhanced_setting_sort_order_integer(): void
    {
        $setting = new EnhancedSetting([
            'key' => 'sort_test',
            'sort_order' => '123',  // String input
        ]);

        $this->assertEquals(123, $setting->sort_order);
        $this->assertIsInt($setting->sort_order);
    }

    public function test_enhanced_setting_handles_null_values(): void
    {
        $setting = new EnhancedSetting([
            'key' => 'null_test',
            'value' => null,
            'description' => null,
            'validation_rules' => null,
        ]);

        $this->assertNull($setting->value);
        $this->assertNull($setting->description);
        $this->assertNull($setting->validation_rules);
    }

    public function test_enhanced_setting_handles_empty_values(): void
    {
        $setting = new EnhancedSetting([
            'key' => 'empty_test',
            'value' => '',
            'description' => '',
        ]);

        $this->assertEquals('', $setting->value);
        $this->assertEquals('', $setting->description);
    }
}
