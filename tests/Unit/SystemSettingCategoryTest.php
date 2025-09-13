<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SystemSettingCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_setting_category_can_be_created(): void
    {
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description',
            'icon' => 'heroicon-o-folder',
            'color' => 'blue',
        ]);

        $this->assertInstanceOf(SystemSettingCategory::class, $category);
        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('test-category', $category->slug);
    }

    public function test_system_setting_category_has_settings(): void
    {
        $category = SystemSettingCategory::factory()->create();
        $setting = SystemSetting::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($category->settings->contains($setting));
        $this->assertCount(1, $category->settings);
    }

    public function test_system_setting_category_has_children(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create();
        $childCategory = SystemSettingCategory::factory()->create(['parent_id' => $parentCategory->id]);

        $this->assertTrue($parentCategory->children->contains($childCategory));
        $this->assertCount(1, $parentCategory->children);
    }

    public function test_system_setting_category_has_parent(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create();
        $childCategory = SystemSettingCategory::factory()->create(['parent_id' => $parentCategory->id]);

        $this->assertEquals($parentCategory->id, $childCategory->parent->first()->id);
    }

    public function test_system_setting_category_scope_active(): void
    {
        SystemSettingCategory::factory()->create(['is_active' => true]);
        SystemSettingCategory::factory()->create(['is_active' => false]);

        $activeCategories = SystemSettingCategory::active()->get();

        $this->assertCount(1, $activeCategories);
        $this->assertTrue($activeCategories->first()->is_active);
    }

    public function test_system_setting_category_scope_ordered(): void
    {
        SystemSettingCategory::factory()->create(['sort_order' => 2, 'name' => 'Second']);
        SystemSettingCategory::factory()->create(['sort_order' => 1, 'name' => 'First']);

        $orderedCategories = SystemSettingCategory::ordered()->get();

        $this->assertEquals('First', $orderedCategories->first()->name);
        $this->assertEquals('Second', $orderedCategories->last()->name);
    }

    public function test_system_setting_category_scope_root(): void
    {
        $rootCategory = SystemSettingCategory::factory()->create(['parent_id' => null]);
        $childCategory = SystemSettingCategory::factory()->create(['parent_id' => $rootCategory->id]);

        $rootCategories = SystemSettingCategory::root()->get();

        $this->assertCount(1, $rootCategories);
        $this->assertEquals($rootCategory->id, $rootCategories->first()->id);
    }

    public function test_system_setting_category_scope_with_settings(): void
    {
        $category = SystemSettingCategory::factory()->create();
        SystemSetting::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        SystemSetting::factory()->create(['category_id' => $category->id, 'is_active' => false]);

        $categoryWithSettings = SystemSettingCategory::withSettings()->find($category->id);

        $this->assertCount(1, $categoryWithSettings->settings);
        $this->assertTrue($categoryWithSettings->settings->first()->is_active);
    }

    public function test_get_settings_count(): void
    {
        $category = SystemSettingCategory::factory()->create();
        SystemSetting::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        SystemSetting::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        SystemSetting::factory()->create(['category_id' => $category->id, 'is_active' => false]);

        $count = $category->getSettingsCount();

        $this->assertEquals(2, $count);
    }

    public function test_get_active_settings_count(): void
    {
        $category = SystemSettingCategory::factory()->create();
        SystemSetting::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        SystemSetting::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        SystemSetting::factory()->create(['category_id' => $category->id, 'is_active' => false]);

        $count = $category->getActiveSettingsCount();

        $this->assertEquals(2, $count);
    }

    public function test_get_public_settings_count(): void
    {
        $category = SystemSettingCategory::factory()->create();
        SystemSetting::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
            'is_public' => true,
        ]);
        SystemSetting::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
            'is_public' => true,
        ]);
        SystemSetting::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
            'is_public' => false,
        ]);

        $count = $category->getPublicSettingsCount();

        $this->assertEquals(2, $count);
    }

    public function test_has_active_settings(): void
    {
        $category = SystemSettingCategory::factory()->create();
        SystemSetting::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        SystemSetting::factory()->create(['category_id' => $category->id, 'is_active' => false]);

        $this->assertTrue($category->hasActiveSettings());
    }

    public function test_has_public_settings(): void
    {
        $category = SystemSettingCategory::factory()->create();
        SystemSetting::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
            'is_public' => true,
        ]);
        SystemSetting::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
            'is_public' => false,
        ]);

        $this->assertTrue($category->hasPublicSettings());
    }

    public function test_get_icon_class(): void
    {
        $category = SystemSettingCategory::factory()->create(['icon' => 'heroicon-o-folder']);
        $categoryWithoutIcon = SystemSettingCategory::factory()->create(['icon' => null]);

        $this->assertEquals('heroicon-o-folder', $category->getIconClass());
        $this->assertEquals('heroicon-o-cog-6-tooth', $categoryWithoutIcon->getIconClass());
    }

    public function test_get_color_class(): void
    {
        $blueCategory = SystemSettingCategory::factory()->create(['color' => 'primary']);
        $redCategory = SystemSettingCategory::factory()->create(['color' => 'danger']);

        $this->assertEquals('text-primary-600', $blueCategory->getColorClass());
        $this->assertEquals('text-danger-600', $redCategory->getColorClass());
    }

    public function test_get_badge_color_class(): void
    {
        $blueCategory = SystemSettingCategory::factory()->create(['color' => 'primary']);
        $redCategory = SystemSettingCategory::factory()->create(['color' => 'danger']);

        $this->assertEquals('bg-primary-100 text-primary-800', $blueCategory->getBadgeColorClass());
        $this->assertEquals('bg-danger-100 text-danger-800', $redCategory->getBadgeColorClass());
    }

    public function test_is_root(): void
    {
        $rootCategory = SystemSettingCategory::factory()->create(['parent_id' => null]);
        $childCategory = SystemSettingCategory::factory()->create(['parent_id' => $rootCategory->id]);

        $this->assertTrue($rootCategory->isRoot());
        $this->assertFalse($childCategory->isRoot());
    }

    public function test_is_leaf(): void
    {
        $leafCategory = SystemSettingCategory::factory()->create();
        $parentCategory = SystemSettingCategory::factory()->create();
        SystemSettingCategory::factory()->create(['parent_id' => $parentCategory->id]);

        $this->assertTrue($leafCategory->isLeaf());
        $this->assertFalse($parentCategory->isLeaf());
    }

    public function test_get_parent(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create();
        $childCategory = SystemSettingCategory::factory()->create(['parent_id' => $parentCategory->id]);

        $parent = $childCategory->getParent();

        $this->assertEquals($parentCategory->id, $parent->id);
    }

    public function test_get_children(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create();
        $childCategory1 = SystemSettingCategory::factory()->create(['parent_id' => $parentCategory->id, 'is_active' => true]);
        $childCategory2 = SystemSettingCategory::factory()->create(['parent_id' => $parentCategory->id, 'is_active' => false]);

        $children = $parentCategory->getChildren();

        $this->assertCount(1, $children);
        $this->assertEquals($childCategory1->id, $children->first()->id);
    }

    public function test_get_all_children(): void
    {
        $rootCategory = SystemSettingCategory::factory()->create();
        $childCategory1 = SystemSettingCategory::factory()->create(['parent_id' => $rootCategory->id]);
        $childCategory2 = SystemSettingCategory::factory()->create(['parent_id' => $childCategory1->id]);

        $allChildren = $rootCategory->getAllChildren();

        $this->assertCount(2, $allChildren);
        $this->assertTrue($allChildren->contains($childCategory1));
        $this->assertTrue($allChildren->contains($childCategory2));
    }

    public function test_get_depth(): void
    {
        $rootCategory = SystemSettingCategory::factory()->create(['parent_id' => null]);
        $childCategory = SystemSettingCategory::factory()->create(['parent_id' => $rootCategory->id]);
        $grandChildCategory = SystemSettingCategory::factory()->create(['parent_id' => $childCategory->id]);

        $this->assertEquals(0, $rootCategory->getDepth());
        $this->assertEquals(1, $childCategory->getDepth());
        $this->assertEquals(2, $grandChildCategory->getDepth());
    }

    public function test_get_path(): void
    {
        $rootCategory = SystemSettingCategory::factory()->create(['name' => 'Root']);
        $childCategory = SystemSettingCategory::factory()->create(['name' => 'Child', 'parent_id' => $rootCategory->id]);

        $path = $childCategory->getPath();

        $this->assertEquals('Root > Child', $path);
    }

    public function test_get_breadcrumb(): void
    {
        $rootCategory = SystemSettingCategory::factory()->create(['name' => 'Root', 'slug' => 'root']);
        $childCategory = SystemSettingCategory::factory()->create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $rootCategory->id]);

        $breadcrumb = $childCategory->getBreadcrumb();

        $this->assertCount(2, $breadcrumb);
        $this->assertEquals('Root', $breadcrumb[0]['name']);
        $this->assertEquals('Child', $breadcrumb[1]['name']);
    }

    public function test_get_tree_structure(): void
    {
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description',
            'icon' => 'heroicon-o-folder',
            'color' => 'blue',
        ]);

        SystemSetting::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        SystemSetting::factory()->create(['category_id' => $category->id, 'is_active' => true, 'is_public' => true]);

        $tree = $category->getTreeStructure();

        $this->assertEquals('Test Category', $tree['name']);
        $this->assertEquals('test-category', $tree['slug']);
        $this->assertEquals('Test description', $tree['description']);
        $this->assertEquals('heroicon-o-folder', $tree['icon']);
        $this->assertEquals('blue', $tree['color']);
        $this->assertEquals(2, $tree['settings_count']);
        $this->assertEquals(1, $tree['public_settings_count']);
        $this->assertIsArray($tree['children']);
    }

    public function test_get_settings_by_group(): void
    {
        $category = SystemSettingCategory::factory()->create();
        SystemSetting::factory()->create([
            'category_id' => $category->id,
            'group' => 'general',
            'is_active' => true,
        ]);
        SystemSetting::factory()->create([
            'category_id' => $category->id,
            'group' => 'ecommerce',
            'is_active' => true,
        ]);
        SystemSetting::factory()->create([
            'category_id' => $category->id,
            'group' => 'general',
            'is_active' => true,
        ]);

        $settingsByGroup = $category->getSettingsByGroup();

        $this->assertCount(2, $settingsByGroup);
        $this->assertCount(2, $settingsByGroup['general']);
        $this->assertCount(1, $settingsByGroup['ecommerce']);
    }
}