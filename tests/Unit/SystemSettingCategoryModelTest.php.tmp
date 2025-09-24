<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingCategoryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_system_setting_category(): void
    {
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description',
            'icon' => 'heroicon-o-cog',
            'color' => '#FF0000',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('system_setting_categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description',
            'icon' => 'heroicon-o-cog',
            'color' => '#FF0000',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('test-category', $category->slug);
        $this->assertEquals('Test description', $category->description);
        $this->assertEquals('heroicon-o-cog', $category->icon);
        $this->assertEquals('#FF0000', $category->color);
        $this->assertEquals(1, $category->sort_order);
        $this->assertTrue($category->is_active);
    }

    public function test_fillable_attributes(): void
    {
        $category = new SystemSettingCategory;
        $expectedFillable = [
            'name',
            'slug',
            'description',
            'icon',
            'color',
            'sort_order',
            'is_active',
            'parent_id',
        ];

        $this->assertEquals($expectedFillable, $category->getFillable());
    }

    public function test_casts_attributes(): void
    {
        $category = SystemSettingCategory::factory()->create([
            'is_active' => '1',
            'sort_order' => '5',
            'parent_id' => '10',
        ]);

        $this->assertIsBool($category->is_active);
        $this->assertTrue($category->is_active);
        $this->assertIsInt($category->sort_order);
        $this->assertEquals(5, $category->sort_order);
        $this->assertIsInt($category->parent_id);
        $this->assertEquals(10, $category->parent_id);
    }

    public function test_parent_relationship(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create(['name' => 'Parent Category']);
        $childCategory = SystemSettingCategory::factory()->create([
            'parent_id' => $parentCategory->id,
            'name' => 'Child Category',
        ]);

        $this->assertInstanceOf(SystemSettingCategory::class, $childCategory->parent);
        $this->assertEquals($parentCategory->id, $childCategory->parent->id);
        $this->assertEquals('Parent Category', $childCategory->parent->name);
    }

    public function test_children_relationship(): void
    {
        $parentCategory = SystemSettingCategory::factory()->create(['name' => 'Parent Category']);
        $child1 = SystemSettingCategory::factory()->create([
            'parent_id' => $parentCategory->id,
            'name' => 'Child 1',
        ]);
        $child2 = SystemSettingCategory::factory()->create([
            'parent_id' => $parentCategory->id,
            'name' => 'Child 2',
        ]);

        $children = $parentCategory->children;
        $this->assertCount(2, $children);
        $this->assertTrue($children->contains($child1));
        $this->assertTrue($children->contains($child2));
    }

    public function test_translations_relationship(): void
    {
        $category = SystemSettingCategory::factory()->create(['name' => 'Test Category']);
        $translation = SystemSettingCategoryTranslation::factory()->create([
            'system_setting_category_id' => $category->id,
            'locale' => 'en',
            'name' => 'Test Category EN',
            'description' => 'Test description EN',
        ]);

        $translations = $category->translations;
        $this->assertCount(1, $translations);
        $this->assertTrue($translations->contains($translation));
        $this->assertEquals('Test Category EN', $translations->first()->name);
    }

    public function test_active_scope(): void
    {
        $activeCategory = SystemSettingCategory::factory()->create(['is_active' => true]);
        $inactiveCategory = SystemSettingCategory::factory()->create(['is_active' => false]);

        $activeCategories = SystemSettingCategory::active()->get();
        $this->assertTrue($activeCategories->contains($activeCategory));
        $this->assertFalse($activeCategories->contains($inactiveCategory));
    }

    public function test_ordered_scope(): void
    {
        $category1 = SystemSettingCategory::factory()->create(['sort_order' => 3, 'name' => 'Third']);
        $category2 = SystemSettingCategory::factory()->create(['sort_order' => 1, 'name' => 'First']);
        $category3 = SystemSettingCategory::factory()->create(['sort_order' => 2, 'name' => 'Second']);

        $orderedCategories = SystemSettingCategory::ordered()->get();
        $this->assertEquals($category2->id, $orderedCategories->first()->id);
        $this->assertEquals($category3->id, $orderedCategories->skip(1)->first()->id);
        $this->assertEquals($category1->id, $orderedCategories->last()->id);
    }

    public function test_root_scope(): void
    {
        $rootCategory = SystemSettingCategory::factory()->create(['parent_id' => null]);
        $childCategory = SystemSettingCategory::factory()->create([
            'parent_id' => $rootCategory->id,
        ]);

        $rootCategories = SystemSettingCategory::root()->get();
        $this->assertTrue($rootCategories->contains($rootCategory));
        $this->assertFalse($rootCategories->contains($childCategory));
    }

    public function test_get_translated_name(): void
    {
        $category = SystemSettingCategory::factory()->create(['name' => 'Default Name']);

        // Test with default name (no translation)
        $this->assertEquals('Default Name', $category->getTranslatedName());
        $this->assertEquals('Default Name', $category->getTranslatedName('en'));

        // Test with translation
        SystemSettingCategoryTranslation::factory()->create([
            'system_setting_category_id' => $category->id,
            'locale' => 'en',
            'name' => 'English Name',
        ]);

        $this->assertEquals('English Name', $category->getTranslatedName('en'));
        $this->assertEquals('Default Name', $category->getTranslatedName('lt')); // No translation for LT
    }

    public function test_get_translated_description(): void
    {
        $category = SystemSettingCategory::factory()->create(['description' => 'Default Description']);

        // Test with default description (no translation)
        $this->assertEquals('Default Description', $category->getTranslatedDescription());
        $this->assertEquals('Default Description', $category->getTranslatedDescription('en'));

        // Test with translation
        SystemSettingCategoryTranslation::factory()->create([
            'system_setting_category_id' => $category->id,
            'locale' => 'en',
            'description' => 'English Description',
        ]);

        $this->assertEquals('English Description', $category->getTranslatedDescription('en'));
        $this->assertEquals('Default Description', $category->getTranslatedDescription('lt')); // No translation for LT
    }

    public function test_soft_deletes(): void
    {
        $category = SystemSettingCategory::factory()->create();
        $categoryId = $category->id;

        $category->delete();

        $this->assertSoftDeleted('system_setting_categories', ['id' => $categoryId]);
    }
}
