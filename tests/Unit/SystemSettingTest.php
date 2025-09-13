<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingHistory;
use App\Models\SystemSettingDependency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SystemSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_setting_belongs_to_category(): void
    {
        $category = SystemSettingCategory::factory()->create();
        $setting = SystemSetting::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(SystemSettingCategory::class, $setting->category);
        $this->assertEquals($category->id, $setting->category->id);
    }

    public function test_system_setting_belongs_to_updated_by_user(): void
    {
        $user = User::factory()->create();
        $setting = SystemSetting::factory()->create(['updated_by' => $user->id]);

        $this->assertInstanceOf(User::class, $setting->updatedBy);
        $this->assertEquals($user->id, $setting->updatedBy->id);
    }

    public function test_system_setting_has_many_translations(): void
    {
        $setting = SystemSetting::factory()->create();
        
        // Create translations
        $setting->translations()->create([
            'locale' => 'lt',
            'name' => 'Lietuviškas pavadinimas',
            'description' => 'Lietuviškas aprašymas',
        ]);

        $setting->translations()->create([
            'locale' => 'en',
            'name' => 'English Name',
            'description' => 'English Description',
        ]);

        $this->assertCount(2, $setting->translations);
        $this->assertEquals('Lietuviškas pavadinimas', $setting->getTranslatedName('lt'));
        $this->assertEquals('English Name', $setting->getTranslatedName('en'));
    }

    public function test_system_setting_has_many_history_records(): void
    {
        $setting = SystemSetting::factory()->create();
        
        $setting->history()->create([
            'old_value' => 'old',
            'new_value' => 'new',
            'changed_by' => User::factory()->create()->id,
            'change_reason' => 'Test change',
        ]);

        $this->assertCount(1, $setting->history);
        $this->assertInstanceOf(SystemSettingHistory::class, $setting->history->first());
    }

    public function test_system_setting_has_many_dependencies(): void
    {
        $setting1 = SystemSetting::factory()->create();
        $setting2 = SystemSetting::factory()->create();

        $dependency = SystemSettingDependency::create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'equals',
            'condition_value' => 'test',
        ]);

        $this->assertCount(1, $setting1->dependencies);
        $this->assertCount(1, $setting2->dependents);
        $this->assertInstanceOf(SystemSettingDependency::class, $setting1->dependencies->first());
    }

    public function test_system_setting_category_has_many_settings(): void
    {
        $category = SystemSettingCategory::factory()->create();
        
        $setting1 = SystemSetting::factory()->create(['category_id' => $category->id]);
        $setting2 = SystemSetting::factory()->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->settings);
        $this->assertTrue($category->settings->contains($setting1));
        $this->assertTrue($category->settings->contains($setting2));
    }

    public function test_system_setting_category_can_have_parent_and_children(): void
    {
        $parent = SystemSettingCategory::factory()->create();
        $child = SystemSettingCategory::factory()->create(['parent_id' => $parent->id]);

        $this->assertCount(1, $parent->children);
        $this->assertInstanceOf(SystemSettingCategory::class, $child->getParent());
        $this->assertEquals($parent->id, $child->getParent()->id);
    }

    public function test_system_setting_category_has_translations(): void
    {
        $category = SystemSettingCategory::factory()->create();
        
        $category->translations()->create([
            'locale' => 'lt',
            'name' => 'Lietuviška kategorija',
            'description' => 'Lietuviškas aprašymas',
        ]);

        $this->assertCount(1, $category->translations);
        $this->assertEquals('Lietuviška kategorija', $category->getTranslatedName('lt'));
    }

    public function test_system_setting_category_scopes(): void
    {
        SystemSettingCategory::factory()->create(['is_active' => true]);
        SystemSettingCategory::factory()->create(['is_active' => false]);
        SystemSettingCategory::factory()->create(['parent_id' => null]);
        SystemSettingCategory::factory()->create(['parent_id' => 1]);

        $activeCategories = SystemSettingCategory::active()->get();
        $this->assertCount(1, $activeCategories);

        $rootCategories = SystemSettingCategory::root()->get();
        $this->assertCount(1, $rootCategories);
    }

    public function test_system_setting_category_helper_methods(): void
    {
        $category = SystemSettingCategory::factory()->create([
            'icon' => 'heroicon-o-cog',
            'color' => 'primary',
        ]);

        $this->assertEquals('heroicon-o-cog', $category->getIconClass());
        $this->assertStringContainsString('primary', $category->getColorClass());
        $this->assertStringContainsString('primary', $category->getBadgeColorClass());
    }

    public function test_system_setting_category_tree_methods(): void
    {
        $parent = SystemSettingCategory::factory()->create(['name' => 'Parent']);
        $child = SystemSettingCategory::factory()->create(['name' => 'Child', 'parent_id' => $parent->id]);

        $this->assertEquals('Parent > Child', $child->getPath());
        $this->assertEquals(1, $child->getDepth());
        $this->assertTrue($parent->isRoot());
        $this->assertTrue($child->isLeaf());
        $this->assertFalse($parent->isLeaf());
    }

    public function test_system_setting_history_belongs_to_setting(): void
    {
        $setting = SystemSetting::factory()->create();
        $history = SystemSettingHistory::factory()->create(['system_setting_id' => $setting->id]);

        $this->assertInstanceOf(SystemSetting::class, $history->systemSetting);
        $this->assertEquals($setting->id, $history->systemSetting->id);
    }

    public function test_system_setting_history_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $history = SystemSettingHistory::factory()->create(['changed_by' => $user->id]);

        $this->assertInstanceOf(User::class, $history->changedBy);
        $this->assertEquals($user->id, $history->changedBy->id);
    }

    public function test_system_setting_history_scopes(): void
    {
        $setting = SystemSetting::factory()->create();
        
        SystemSettingHistory::factory()->create([
            'system_setting_id' => $setting->id,
            'created_at' => now()->subDays(5),
        ]);
        
        SystemSettingHistory::factory()->create([
            'system_setting_id' => $setting->id,
            'created_at' => now()->subDays(35),
        ]);

        $recentHistory = SystemSettingHistory::recent(30)->get();
        $this->assertCount(1, $recentHistory);

        $settingHistory = SystemSettingHistory::bySetting($setting->id)->get();
        $this->assertCount(2, $settingHistory);
    }

    public function test_system_setting_history_change_type_detection(): void
    {
        $history1 = SystemSettingHistory::factory()->create(['old_value' => null, 'new_value' => 'new']);
        $history2 = SystemSettingHistory::factory()->create(['old_value' => 'old', 'new_value' => null]);
        $history3 = SystemSettingHistory::factory()->create(['old_value' => 'old', 'new_value' => 'new']);

        $this->assertEquals('created', $history1->getChangeType());
        $this->assertEquals('deleted', $history2->getChangeType());
        $this->assertEquals('updated', $history3->getChangeType());
    }

    public function test_system_setting_dependency_belongs_to_settings(): void
    {
        $setting1 = SystemSetting::factory()->create();
        $setting2 = SystemSetting::factory()->create();
        
        $dependency = SystemSettingDependency::create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'equals',
            'condition_value' => 'test',
        ]);

        $this->assertInstanceOf(SystemSetting::class, $dependency->setting);
        $this->assertInstanceOf(SystemSetting::class, $dependency->dependsOnSetting);
        $this->assertEquals($setting1->id, $dependency->setting->id);
        $this->assertEquals($setting2->id, $dependency->dependsOnSetting->id);
    }

    public function test_system_setting_dependency_condition_types(): void
    {
        $setting1 = SystemSetting::factory()->create(['value' => 'test']);
        $setting2 = SystemSetting::factory()->create();

        $conditions = [
            'equals' => 'test',
            'not_equals' => 'different',
            'greater_than' => '5',
            'less_than' => '10',
            'contains' => 'es',
            'not_contains' => 'xyz',
            'is_empty' => '',
            'is_not_empty' => 'test',
            'is_true' => '1',
            'is_false' => '0',
        ];

        foreach ($conditions as $condition => $value) {
            $dependency = SystemSettingDependency::create([
                'setting_id' => $setting2->id,
                'depends_on_setting_id' => $setting1->id,
                'condition' => $condition,
                'condition_value' => $value,
            ]);

            $this->assertTrue($dependency->isConditionMet(), "Condition {$condition} should be met");
        }
    }

    public function test_system_setting_dependency_scopes(): void
    {
        SystemSettingDependency::factory()->create(['is_active' => true, 'condition' => 'equals']);
        SystemSettingDependency::factory()->create(['is_active' => false, 'condition' => 'equals']);
        SystemSettingDependency::factory()->create(['is_active' => true, 'condition' => 'not_equals']);

        $activeDependencies = SystemSettingDependency::active()->get();
        $this->assertCount(2, $activeDependencies);

        $equalsDependencies = SystemSettingDependency::byCondition('equals')->get();
        $this->assertCount(2, $equalsDependencies);
    }
}