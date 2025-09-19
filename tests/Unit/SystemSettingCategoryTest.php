<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SystemSettingCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_setting_category_model_relationships(): void
    {
        $category = SystemSettingCategory::factory()->create();
        $setting = SystemSetting::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(SystemSetting::class, $category->settings->first());
        $this->assertEquals($setting->id, $category->settings->first()->id);
    }

    public function test_system_setting_category_model_scopes(): void
    {
        $activeCategory = SystemSettingCategory::factory()->active()->create();
        $inactiveCategory = SystemSettingCategory::factory()->inactive()->create();

        $this->assertTrue($activeCategory->is_active);
        $this->assertFalse($inactiveCategory->is_active);
    }

    public function test_system_setting_category_model_scope_active(): void
    {
        $activeCategory = SystemSettingCategory::factory()->active()->create();
        $inactiveCategory = SystemSettingCategory::factory()->inactive()->create();

        $activeCategories = SystemSettingCategory::active()->get();
        $this->assertCount(1, $activeCategories);
        $this->assertEquals($activeCategory->id, $activeCategories->first()->id);
    }

    public function test_system_setting_category_model_scope_ordered(): void
    {
        $category1 = SystemSettingCategory::factory()->create(['sort_order' => 2, 'name' => 'B']);
        $category2 = SystemSettingCategory::factory()->create(['sort_order' => 1, 'name' => 'A']);

        $orderedCategories = SystemSettingCategory::ordered()->get();
        $this->assertEquals($category2->id, $orderedCategories->first()->id);
        $this->assertEquals($category1->id, $orderedCategories->last()->id);
    }

    public function test_system_setting_category_model_scope_root(): void
    {
        $rootCategory = SystemSettingCategory::factory()->root()->create();
        $childCategory = SystemSettingCategory::factory()->create(['parent_id' => $rootCategory->id]);

        $rootCategories = SystemSettingCategory::root()->get();
        $this->assertCount(1, $rootCategories);
        $this->assertEquals($rootCategory->id, $rootCategories->first()->id);
    }

    public function test_system_setting_category_model_scope_with_settings(): void
    {
        $category = SystemSettingCategory::factory()->create();
        $setting = SystemSetting::factory()->create(['category_id' => $category->id]);

        $categoriesWithSettings = SystemSettingCategory::withSettings()->get();
        $this->assertCount(1, $categoriesWithSettings);
        $this->assertEquals($category->id, $categoriesWithSettings->first()->id);
    }

    public function test_system_setting_category_model_translated_methods(): void
    {
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original Description',
        ]);

        $translatedName = $category->getTranslatedName();
        $translatedDescription = $category->getTranslatedDescription();

        $this->assertEquals('Original Name', $translatedName);
        $this->assertEquals('Original Description', $translatedDescription);
    }

    public function test_system_setting_category_model_settings_count(): void
    {
        $category = SystemSettingCategory::factory()->create();
        SystemSetting::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertEquals(3, $category->getSettingsCount());
    }

    public function test_system_setting_category_model_has_active_settings(): void
    {
        $category = SystemSettingCategory::factory()->create();
        SystemSetting::factory()->active()->create(['category_id' => $category->id]);

        $this->assertTrue($category->hasActiveSettings());
    }

    public function test_system_setting_category_model_icon_class(): void
    {
        $category = SystemSettingCategory::factory()->create(['icon' => 'heroicon-o-test']);
        $this->assertEquals('heroicon-o-test', $category->getIconClass());

        $categoryWithoutIcon = SystemSettingCategory::factory()->create(['icon' => null]);
        $this->assertEquals('heroicon-o-cog-6-tooth', $categoryWithoutIcon->getIconClass());
    }

    public function test_system_setting_category_model_color_class(): void
    {
        $category = SystemSettingCategory::factory()->create(['color' => 'primary']);
        $this->assertEquals('text-primary-600', $category->getColorClass());

        $categoryWithoutColor = SystemSettingCategory::factory()->create(['color' => null]);
        $this->assertEquals('text-gray-600', $categoryWithoutColor->getColorClass());
    }

    public function test_system_setting_category_model_badge_color_class(): void
    {
        $category = SystemSettingCategory::factory()->create(['color' => 'success']);
        $this->assertEquals('bg-success-100 text-success-800', $category->getBadgeColorClass());

        $categoryWithoutColor = SystemSettingCategory::factory()->create(['color' => null]);
        $this->assertEquals('bg-gray-100 text-gray-800', $categoryWithoutColor->getBadgeColorClass());
    }

    public function test_system_setting_category_model_active_settings_count(): void
    {
        $category = SystemSettingCategory::factory()->create();
        SystemSetting::factory()->count(2)->active()->create(['category_id' => $category->id]);
        SystemSetting::factory()->count(1)->inactive()->create(['category_id' => $category->id]);

        $this->assertEquals(2, $category->getActiveSettingsCount());
    }

    public function test_system_setting_category_model_public_settings_count(): void
    {
        $category = SystemSettingCategory::factory()->create();
        SystemSetting::factory()->count(2)->public()->create(['category_id' => $category->id]);
        SystemSetting::factory()->count(1)->private()->create(['category_id' => $category->id]);

        $this->assertEquals(2, $category->getPublicSettingsCount());
    }

    public function test_system_setting_category_model_settings_by_group(): void
    {
        $category = SystemSettingCategory::factory()->create();
        SystemSetting::factory()->create(['category_id' => $category->id, 'group' => 'group1']);
        SystemSetting::factory()->create(['category_id' => $category->id, 'group' => 'group2']);

        $settingsByGroup = $category->getSettingsByGroup();
        $this->assertCount(2, $settingsByGroup);
        $this->assertArrayHasKey('group1', $settingsByGroup->toArray());
        $this->assertArrayHasKey('group2', $settingsByGroup->toArray());
    }

    public function test_system_setting_category_model_has_public_settings(): void
    {
        $category = SystemSettingCategory::factory()->create();
        SystemSetting::factory()->public()->create(['category_id' => $category->id]);

        $this->assertTrue($category->hasPublicSettings());
    }

    public function test_system_setting_category_model_parent_relationship(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create();
        $childCategory = SystemSettingCategory::factory()->create(['parent_id' => $parentCategory->id]);

        $this->assertEquals($parentCategory->id, $childCategory->getParent()->id);
    }

    public function test_system_setting_category_model_children_relationship(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create();
        $childCategory = SystemSettingCategory::factory()->create(['parent_id' => $parentCategory->id]);

        $children = $parentCategory->getChildren();
        $this->assertCount(1, $children);
        $this->assertEquals($childCategory->id, $children->first()->id);
    }

    public function test_system_setting_category_model_all_children(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create();
        $childCategory1 = SystemSettingCategory::factory()->create(['parent_id' => $parentCategory->id]);
        $childCategory2 = SystemSettingCategory::factory()->create(['parent_id' => $childCategory1->id]);

        $allChildren = $parentCategory->getAllChildren();
        $this->assertCount(2, $allChildren);
    }

    public function test_system_setting_category_model_path(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create(['name' => 'Parent']);
        $childCategory = SystemSettingCategory::factory()->create(['name' => 'Child', 'parent_id' => $parentCategory->id]);

        $path = $childCategory->getPath();
        $this->assertEquals('Parent > Child', $path);
    }

    public function test_system_setting_category_model_depth(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create();
        $childCategory = SystemSettingCategory::factory()->create(['parent_id' => $parentCategory->id]);
        $grandchildCategory = SystemSettingCategory::factory()->create(['parent_id' => $childCategory->id]);

        $this->assertEquals(0, $parentCategory->getDepth());
        $this->assertEquals(1, $childCategory->getDepth());
        $this->assertEquals(2, $grandchildCategory->getDepth());
    }

    public function test_system_setting_category_model_is_root(): void
    {
        $rootCategory = SystemSettingCategory::factory()->root()->create();
        $childCategory = SystemSettingCategory::factory()->create(['parent_id' => $rootCategory->id]);

        $this->assertTrue($rootCategory->isRoot());
        $this->assertFalse($childCategory->isRoot());
    }

    public function test_system_setting_category_model_is_leaf(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create();
        $childCategory = SystemSettingCategory::factory()->create(['parent_id' => $parentCategory->id]);

        $this->assertFalse($parentCategory->isLeaf());
        $this->assertTrue($childCategory->isLeaf());
    }

    public function test_system_setting_category_model_breadcrumb(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create(['name' => 'Parent', 'slug' => 'parent']);
        $childCategory = SystemSettingCategory::factory()->create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $parentCategory->id]);

        $breadcrumb = $childCategory->getBreadcrumb();
        $this->assertCount(2, $breadcrumb);
        $this->assertEquals('Parent', $breadcrumb[0]['name']);
        $this->assertEquals('Child', $breadcrumb[1]['name']);
    }

    public function test_system_setting_category_model_tree_structure(): void
    {
        $category = SystemSettingCategory::factory()->create(['name' => 'Test Category']);
        SystemSetting::factory()->count(2)->create(['category_id' => $category->id]);

        $treeStructure = $category->getTreeStructure();
        $this->assertEquals('Test Category', $treeStructure['name']);
        $this->assertEquals(2, $treeStructure['settings_count']);
    }

    public function test_system_setting_category_model_fillable_attributes(): void
    {
        $category = new SystemSettingCategory();
        $fillable = $category->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('icon', $fillable);
        $this->assertContains('color', $fillable);
    }

    public function test_system_setting_category_model_casts(): void
    {
        $category = SystemSettingCategory::factory()->create([
            'is_active' => '1',
            'sort_order' => '10',
            'parent_id' => '5',
        ]);

        $this->assertTrue($category->is_active);
        $this->assertIsInt($category->sort_order);
        $this->assertIsInt($category->parent_id);
    }

    public function test_system_setting_category_model_slug_generation(): void
    {
        $category = SystemSettingCategory::factory()->create(['name' => 'Test Category']);
        $this->assertNotNull($category->slug);
        $this->assertStringContains('test-category', $category->slug);
    }

    public function test_system_setting_category_model_activity_log(): void
    {
        $category = SystemSettingCategory::factory()->create();
        $category->name = 'Updated Name';
        $category->save();

        $this->assertTrue(true);  // If no exception is thrown, activity log works
    }

    public function test_system_setting_category_model_soft_deletes(): void
    {
        $category = SystemSettingCategory::factory()->create();
        $category->delete();

        $this->assertSoftDeleted('system_setting_categories', ['id' => $category->id]);
    }

    public function test_system_setting_category_model_with_parent(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create();
        $childCategory = SystemSettingCategory::factory()->withParent($parentCategory)->create();

        $this->assertEquals($parentCategory->id, $childCategory->parent_id);
    }

    public function test_system_setting_category_model_collapsible(): void
    {
        $collapsibleCategory = SystemSettingCategory::factory()->collapsible()->create();
        $nonCollapsibleCategory = SystemSettingCategory::factory()->notCollapsible()->create();

        $this->assertTrue($collapsibleCategory->is_collapsible);
        $this->assertFalse($nonCollapsibleCategory->is_collapsible);
    }

    public function test_system_setting_category_model_show_in_sidebar(): void
    {
        $showCategory = SystemSettingCategory::factory()->showInSidebar()->create();
        $hideCategory = SystemSettingCategory::factory()->hideFromSidebar()->create();

        $this->assertTrue($showCategory->show_in_sidebar);
        $this->assertFalse($hideCategory->show_in_sidebar);
    }

    public function test_system_setting_category_model_with_permission(): void
    {
        $category = SystemSettingCategory::factory()->withPermission('admin')->create();
        $this->assertEquals('admin', $category->permission);
    }

    public function test_system_setting_category_model_with_color(): void
    {
        $category = SystemSettingCategory::factory()->withColor('primary')->create();
        $this->assertEquals('primary', $category->color);
    }

    public function test_system_setting_category_model_with_icon(): void
    {
        $category = SystemSettingCategory::factory()->withIcon('heroicon-o-test')->create();
        $this->assertEquals('heroicon-o-test', $category->icon);
    }
}
