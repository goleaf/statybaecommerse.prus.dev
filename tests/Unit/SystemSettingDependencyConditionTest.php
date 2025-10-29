<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SystemSetting;
use App\Models\SystemSettingDependency;
use Closure;
use Database\Factories\SystemSettingDependencyFactory;
use Database\Factories\SystemSettingFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
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
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'feature_enabled',
            'value' => 'yes',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->equals('yes')
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_equals_condition_returns_false_when_values_dont_match(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'feature_enabled',
            'value' => 'no',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->equals('yes')
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_equals_condition_handles_boolean_setting_values(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'feature_enabled',
            'value' => true,
        ], static fn (SystemSettingFactory $factory): SystemSettingFactory => $factory->boolean());

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->equals('true')
        );

        // Boolean-backed settings should evaluate truthy string comparisons correctly.
        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_not_equals_condition_returns_true_when_values_differ(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'feature_enabled',
            'value' => 'yes',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->notEquals('no')
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_not_equals_condition_returns_false_when_values_match(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'feature_enabled',
            'value' => 'yes',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->notEquals('yes')
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_greater_than_condition_with_numeric_values(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'max_users',
            'value' => '100',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->greaterThan('50')
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_greater_than_condition_returns_false_when_equal(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'max_users',
            'value' => '100',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->greaterThan('100')
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_less_than_condition_with_numeric_values(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'min_users',
            'value' => '10',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->lessThan('50')
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_less_than_condition_returns_false_when_equal(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'min_users',
            'value' => '50',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->lessThan('50')
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_contains_condition_returns_true_when_substring_found(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'allowed_roles',
            'value' => 'admin,editor,viewer',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->contains('editor')
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_contains_condition_returns_false_when_substring_not_found(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'allowed_roles',
            'value' => 'admin,viewer',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->contains('editor')
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_not_contains_condition_returns_true_when_substring_not_found(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'allowed_roles',
            'value' => 'admin,viewer',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->notContains('editor')
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_empty_condition_returns_true_when_value_is_empty(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'optional_field',
            'value' => '',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isEmpty()
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_empty_condition_returns_false_when_value_is_not_empty(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'optional_field',
            'value' => 'some_value',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isEmpty()
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_is_not_empty_condition_returns_true_when_value_is_not_empty(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'required_field',
            'value' => 'some_value',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isNotEmpty()
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_not_empty_condition_returns_false_when_value_is_empty(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'required_field',
            'value' => '',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isNotEmpty()
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_is_true_condition_with_boolean_true(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'feature_enabled',
            'value' => 'true',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isTrue()
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_true_condition_with_numeric_one(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'feature_enabled',
            'value' => '1',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isTrue()
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_false_condition_with_boolean_false(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'feature_disabled',
            'value' => 'false',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isFalse()
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_false_condition_with_numeric_zero(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'feature_disabled',
            'value' => '0',
        ]);

        $dependency = $this->createDependency(
            ['depends_on_setting_id' => $dependsOnSetting->id],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isFalse()
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_condition_returns_false_when_depends_on_setting_is_null(): void
    {
        $dependency = $this->createDependency(
            [],
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->equals('test')
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_condition_returns_false_when_condition_is_empty(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'test_setting',
            'value' => 'test_value',
        ]);

        $dependency = $this->createDependency([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => '',
            'condition_value'       => 'test',
        ]);

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_condition_returns_false_when_required_value_is_missing(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'test_setting',
            'value' => 'test_value',
        ]);

        $dependency = $this->createDependency([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'equals',
            'condition_value'       => null,
        ]);

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_starts_with_condition_returns_true_when_prefix_matches(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'api_endpoint',
            'value' => 'https://api.example.com',
        ]);

        $dependency = $this->createDependency([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'starts_with',
            'condition_value'       => 'https://',
        ]);

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_ends_with_condition_returns_true_when_suffix_matches(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'email_address',
            'value' => 'admin@example.com',
        ]);

        $dependency = $this->createDependency([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'ends_with',
            'condition_value'       => '.com',
        ]);

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_in_condition_returns_true_when_value_in_comma_separated_list(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'selected_role',
            'value' => 'editor',
        ]);

        $dependency = $this->createDependency([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'in',
            'condition_value'       => 'admin,editor,viewer',
        ]);

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_in_condition_returns_true_when_value_in_json_array(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'selected_role',
            'value' => 'editor',
        ]);

        $dependency = $this->createDependency([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'in',
            'condition_value'       => '["admin","editor","viewer"]',
        ]);

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_not_in_condition_returns_true_when_value_not_in_list(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'selected_role',
            'value' => 'guest',
        ]);

        $dependency = $this->createDependency([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'not_in',
            'condition_value'       => 'admin,editor,viewer',
        ]);

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_greater_or_equals_condition_returns_true_when_equal(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'count',
            'value' => '100',
        ]);

        $dependency = $this->createDependency([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'greater_or_equals',
            'condition_value'       => '100',
        ]);

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_less_or_equals_condition_returns_true_when_equal(): void
    {
        $dependsOnSetting = $this->createSystemSetting([
            'key'   => 'count',
            'value' => '50',
        ]);

        $dependency = $this->createDependency([
            'depends_on_setting_id' => $dependsOnSetting->id,
            'condition'             => 'less_or_equals',
            'condition_value'       => '50',
        ]);

        $this->assertTrue($dependency->isConditionMet());
    }

    /**
     * Create a system setting without firing attribution observers so the tests
     * can focus on dependency evaluation instead of seeding synthetic users.
     *
     * @param array<string, mixed>                                    $attributes
     * @param Closure(SystemSettingFactory):SystemSettingFactory|null $factoryMutator
     */
    private function createSystemSetting(array $attributes, ?Closure $factoryMutator = null): SystemSetting
    {
        /** @var SystemSettingFactory $factory */
        $factory = SystemSetting::factory();

        if ($factoryMutator instanceof Closure) {
            /** @var SystemSettingFactory $factory */
            $factory = $factoryMutator($factory);
        }

        // Quiet creation bypasses attribution hooks that expect real user records.
        /** @var SystemSetting $setting */
        $setting = $factory->createQuietly($attributes);

        return $setting;
    }

    /**
     * Generate a dependency while eagerly loading the related setting so each
     * test can assert against typed models without additional boilerplate.
     *
     * @param array<string, mixed>                                                        $attributes
     * @param Closure(SystemSettingDependencyFactory):SystemSettingDependencyFactory|null $factoryMutator
     */
    private function createDependency(array $attributes = [], ?Closure $factoryMutator = null): SystemSettingDependency
    {
        /** @var SystemSettingDependencyFactory $factory */
        $factory = SystemSettingDependency::factory();

        if ($factoryMutator instanceof Closure) {
            /** @var SystemSettingDependencyFactory $factory */
            $factory = $factoryMutator($factory);
        }

        /** @var SystemSettingDependency $dependency */
        $dependency = $factory->create($attributes);

        // Preload the dependency relationship once to keep the assertions clean.
        return $dependency->load('dependsOnSettingRelation');
    }
}
