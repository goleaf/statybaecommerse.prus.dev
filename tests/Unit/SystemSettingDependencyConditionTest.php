<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingDependency;
use Database\Factories\SystemSettingDependencyFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SystemSettingDependencyConditionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Explicitly store the reusable category so every fabricated system setting
     * references a persisted parent record and satisfies the FK constraint during tests.
     */
    private SystemSettingCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Creating a predictable active category up front keeps the SQLite
        // foreign keys satisfied without relying on nested factory closures.
        $this->category = SystemSettingCategory::factory()
            ->active()
            ->create([
                'name' => 'General',
            ]);
    }

    /**
     * Provide a dependency factory that already wires the owning setting into
     * the shared category so each dependency instance has a valid relationship graph.
     */
    private function dependencyFactory(): SystemSettingDependencyFactory
    {
        return SystemSettingDependency::factory()
            ->for(SystemSetting::factory()->inCategory($this->category), 'setting')
            ->for(SystemSetting::factory()->inCategory($this->category), 'dependsOnSettingRelation');
    }

    /**
     * Centralise eager-loading so static analysis retains the concrete dependency type.
     *
     * @param SystemSettingDependency $dependency
     */
    private function loadDependency(Model $dependency): SystemSettingDependency
    {
        $dependency->load('dependsOnSettingRelation');

        return $dependency;
    }

    /**
     * Build a dependency with any chained factory modifiers while keeping analysis helpers informed.
     *
     * @param callable(SystemSettingDependencyFactory): SystemSettingDependencyFactory $factoryCallback
     * @param array<string, mixed>                                                     $overrides
     */
    private function makeDependency(callable $factoryCallback, array $overrides = []): SystemSettingDependency
    {
        $factory = $factoryCallback($this->dependencyFactory());

        /** @var SystemSettingDependency $dependency */
        $dependency = $factory->create($overrides);

        return $this->loadDependency($dependency);
    }

    public function test_equals_condition_returns_true_when_values_match(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'feature_enabled',
            'value' => 'yes',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->equals('yes'),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_equals_condition_returns_false_when_values_dont_match(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'feature_enabled',
            'value' => 'no',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->equals('yes'),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_equals_condition_handles_boolean_setting_values(): void
    {
        $dependsOnSetting = SystemSetting::factory()
            ->boolean()
            ->inCategory($this->category)
            ->create([
                'key'   => 'feature_enabled',
                'value' => true,
            ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->equals('true'),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        // Boolean-backed settings should evaluate truthy string comparisons correctly.
        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_not_equals_condition_returns_true_when_values_differ(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'feature_enabled',
            'value' => 'yes',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->notEquals('no'),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_not_equals_condition_returns_false_when_values_match(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'feature_enabled',
            'value' => 'yes',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->notEquals('yes'),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_greater_than_condition_with_numeric_values(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'max_users',
            'value' => '100',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->greaterThan('50'),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_greater_than_condition_returns_false_when_equal(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'max_users',
            'value' => '100',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->greaterThan('100'),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_less_than_condition_with_numeric_values(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'min_users',
            'value' => '10',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->lessThan('50'),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_less_than_condition_returns_false_when_equal(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'min_users',
            'value' => '50',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->lessThan('50'),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_contains_condition_returns_true_when_substring_found(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'allowed_roles',
            'value' => 'admin,editor,viewer',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->contains('editor'),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_contains_condition_returns_false_when_substring_not_found(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'allowed_roles',
            'value' => 'admin,viewer',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->contains('editor'),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_not_contains_condition_returns_true_when_substring_not_found(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'allowed_roles',
            'value' => 'admin,viewer',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->notContains('editor'),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_empty_condition_returns_true_when_value_is_empty(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'optional_field',
            'value' => '',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isEmpty(),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_empty_condition_returns_false_when_value_is_not_empty(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'optional_field',
            'value' => 'some_value',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isEmpty(),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_is_not_empty_condition_returns_true_when_value_is_not_empty(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'required_field',
            'value' => 'some_value',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isNotEmpty(),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_not_empty_condition_returns_false_when_value_is_empty(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'required_field',
            'value' => '',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isNotEmpty(),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_is_true_condition_with_boolean_true(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'feature_enabled',
            'value' => 'true',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isTrue(),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_true_condition_with_numeric_one(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'feature_enabled',
            'value' => '1',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isTrue(),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_false_condition_with_boolean_false(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'feature_disabled',
            'value' => 'false',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isFalse(),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_is_false_condition_with_numeric_zero(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'feature_disabled',
            'value' => '0',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->isFalse(),
            ['depends_on_setting_id' => $dependsOnSetting->id]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_condition_returns_false_when_depends_on_setting_is_null(): void
    {
        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory->equals('test')
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_condition_returns_false_when_condition_is_empty(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'test_setting',
            'value' => 'test_value',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory,
            [
                'depends_on_setting_id' => $dependsOnSetting->id,
                'condition'             => '',
                'condition_value'       => 'test',
            ]
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_condition_returns_false_when_required_value_is_missing(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'test_setting',
            'value' => 'test_value',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory,
            [
                'depends_on_setting_id' => $dependsOnSetting->id,
                'condition'             => 'equals',
                'condition_value'       => null,
            ]
        );

        $this->assertFalse($dependency->isConditionMet());
    }

    public function test_starts_with_condition_returns_true_when_prefix_matches(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'api_endpoint',
            'value' => 'https://api.example.com',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory,
            [
                'depends_on_setting_id' => $dependsOnSetting->id,
                'condition'             => 'starts_with',
                'condition_value'       => 'https://',
            ]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_ends_with_condition_returns_true_when_suffix_matches(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'email_address',
            'value' => 'admin@example.com',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory,
            [
                'depends_on_setting_id' => $dependsOnSetting->id,
                'condition'             => 'ends_with',
                'condition_value'       => '.com',
            ]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_in_condition_returns_true_when_value_in_comma_separated_list(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'selected_role',
            'value' => 'editor',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory,
            [
                'depends_on_setting_id' => $dependsOnSetting->id,
                'condition'             => 'in',
                'condition_value'       => 'admin,editor,viewer',
            ]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_in_condition_returns_true_when_value_in_json_array(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'selected_role',
            'value' => 'editor',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory,
            [
                'depends_on_setting_id' => $dependsOnSetting->id,
                'condition'             => 'in',
                'condition_value'       => '["admin","editor","viewer"]',
            ]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_not_in_condition_returns_true_when_value_not_in_list(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'selected_role',
            'value' => 'guest',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory,
            [
                'depends_on_setting_id' => $dependsOnSetting->id,
                'condition'             => 'not_in',
                'condition_value'       => 'admin,editor,viewer',
            ]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_greater_or_equals_condition_returns_true_when_equal(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'count',
            'value' => '100',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory,
            [
                'depends_on_setting_id' => $dependsOnSetting->id,
                'condition'             => 'greater_or_equals',
                'condition_value'       => '100',
            ]
        );

        $this->assertTrue($dependency->isConditionMet());
    }

    public function test_less_or_equals_condition_returns_true_when_equal(): void
    {
        $dependsOnSetting = SystemSetting::factory()->inCategory($this->category)->create([
            'key'   => 'count',
            'value' => '50',
        ]);

        $dependency = $this->makeDependency(
            static fn (SystemSettingDependencyFactory $factory): SystemSettingDependencyFactory => $factory,
            [
                'depends_on_setting_id' => $dependsOnSetting->id,
                'condition'             => 'less_or_equals',
                'condition_value'       => '50',
            ]
        );

        $this->assertTrue($dependency->isConditionMet());
    }
}
