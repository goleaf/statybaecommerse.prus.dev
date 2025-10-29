<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SystemSetting;
use App\Models\SystemSettingDependency;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SystemSettingDependencyConditionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure attribution observers skip assigning phantom users so we avoid
        // foreign key constraint failures when tests create settings in isolation.
        Config::set('attribution.system_user_id', null);
        Config::set('attribution.system_user_email', null);
        Config::set('attribution.system_user_name', null);
    }

    public function test_equals_condition_returns_true_when_values_match(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'feature_enabled',
            'value' => 'yes',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->equals('yes')
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_equals_condition_returns_false_when_values_dont_match(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'feature_enabled',
            'value' => 'no',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->equals('yes')
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_equals_condition_handles_boolean_setting_values(): void
    {
        $dependsOnSetting = SystemSetting::factory()
            ->boolean()
            ->create([
                'key'   => 'feature_enabled',
                'value' => true,
            ]);

        $dependency = SystemSettingDependency::factory()
            ->equals('true')
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        // Boolean-backed settings should evaluate truthy string comparisons correctly.
        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_not_equals_condition_returns_true_when_values_differ(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'feature_enabled',
            'value' => 'yes',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->notEquals('no')
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_not_equals_condition_returns_false_when_values_match(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'feature_enabled',
            'value' => 'yes',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->notEquals('yes')
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_greater_than_condition_with_numeric_values(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'max_users',
            'value' => '100',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->greaterThan('50')
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_greater_than_condition_returns_false_when_equal(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'max_users',
            'value' => '100',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->greaterThan('100')
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_less_than_condition_with_numeric_values(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'min_users',
            'value' => '10',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->lessThan('50')
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_less_than_condition_returns_false_when_equal(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'min_users',
            'value' => '50',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->lessThan('50')
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_contains_condition_returns_true_when_substring_found(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'allowed_roles',
            'value' => 'admin,editor,viewer',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->contains('editor')
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_contains_condition_returns_false_when_substring_not_found(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'allowed_roles',
            'value' => 'admin,viewer',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->contains('editor')
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_not_contains_condition_returns_true_when_substring_not_found(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'allowed_roles',
            'value' => 'admin,viewer',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->notContains('editor')
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_empty_condition_returns_true_when_value_is_empty(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'optional_field',
            'value' => '',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->isEmpty()
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_empty_condition_returns_false_when_value_is_not_empty(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'optional_field',
            'value' => 'some_value',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->isEmpty()
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_is_not_empty_condition_returns_true_when_value_is_not_empty(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'required_field',
            'value' => 'some_value',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->isNotEmpty()
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_not_empty_condition_returns_false_when_value_is_empty(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'required_field',
            'value' => '',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->isNotEmpty()
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_is_true_condition_with_boolean_true(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'feature_enabled',
            'value' => 'true',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->isTrue()
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_true_condition_with_numeric_one(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'feature_enabled',
            'value' => '1',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->isTrue()
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_false_condition_with_boolean_false(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'feature_disabled',
            'value' => 'false',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->isFalse()
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_false_condition_with_numeric_zero(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'feature_disabled',
            'value' => '0',
        ]);

        $dependency = SystemSettingDependency::factory()
            ->isFalse()
            ->create(['depends_on_setting_id' => $dependsOnSetting->id]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_condition_returns_false_when_depends_on_setting_is_null(): void
    {
        $dependency = SystemSettingDependency::factory()
            ->equals('test')
            ->create();

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_condition_returns_false_when_condition_is_empty(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'test_setting',
            'value' => 'test_value',
        ]);

        $dependency = SystemSettingDependency::factory()->create([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => '',
            'condition_value'       => 'test',
        ]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_condition_returns_false_when_required_value_is_missing(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'test_setting',
            'value' => 'test_value',
        ]);

        $dependency = SystemSettingDependency::factory()->create([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'equals',
            'condition_value'       => null,
        ]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_starts_with_condition_returns_true_when_prefix_matches(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'api_endpoint',
            'value' => 'https://api.example.com',
        ]);

        $dependency = SystemSettingDependency::factory()->create([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'starts_with',
            'condition_value'       => 'https://',
        ]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_ends_with_condition_returns_true_when_suffix_matches(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'email_address',
            'value' => 'admin@example.com',
        ]);

        $dependency = SystemSettingDependency::factory()->create([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'ends_with',
            'condition_value'       => '.com',
        ]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_in_condition_returns_true_when_value_in_comma_separated_list(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'selected_role',
            'value' => 'editor',
        ]);

        $dependency = SystemSettingDependency::factory()->create([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'in',
            'condition_value'       => 'admin,editor,viewer',
        ]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_in_condition_returns_true_when_value_in_json_array(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'selected_role',
            'value' => 'editor',
        ]);

        $dependency = SystemSettingDependency::factory()->create([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'in',
            'condition_value'       => '["admin","editor","viewer"]',
        ]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_not_in_condition_returns_true_when_value_not_in_list(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'selected_role',
            'value' => 'guest',
        ]);

        $dependency = SystemSettingDependency::factory()->create([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'not_in',
            'condition_value'       => 'admin,editor,viewer',
        ]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_greater_or_equals_condition_returns_true_when_equal(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'count',
            'value' => '100',
        ]);

        $dependency = SystemSettingDependency::factory()->create([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'greater_or_equals',
            'condition_value'       => '100',
        ]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_less_or_equals_condition_returns_true_when_equal(): void
    {
        $dependsOnSetting = SystemSetting::factory()->create([
            'key'   => 'count',
            'value' => '50',
        ]);

        $dependency = SystemSettingDependency::factory()->create([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'less_or_equals',
            'condition_value'       => '50',
        ]);

        $dependency->load('dependsOnSettingRelation');

        $this->assertTrue($dependency->isConditionMet());
    }
}
