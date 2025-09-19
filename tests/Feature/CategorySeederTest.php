<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Translations\CategoryTranslation;
use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_seeder_creates_categories_with_translations(): void
    {
        // Arrange
        $seeder = new CategorySeeder();

        // Act
        $seeder->run();

        // Assert
        $this->assertDatabaseCount('categories', 128);
        $this->assertDatabaseCount('category_translations', 512);

        // Check main categories exist
        $this->assertDatabaseHas('categories', [
            'name' => 'Sandarinimo plėvelės ir juostos',
            'slug' => 'sandarinimo-pleveles-ir-juostos',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Tvirtinimo elementai, varžtai, medvarsčiai',
            'slug' => 'tvirtinimo-elementai-varztai-medvarsciai',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Chemija statyboms',
            'slug' => 'chemija-statyboms',
        ]);
    }

    public function test_category_seeder_creates_hierarchical_structure(): void
    {
        // Arrange
        $seeder = new CategorySeeder();

        // Act
        $seeder->run();

        // Assert - Check parent-child relationships
        $parentCategory = Category::where('name', 'Sandarinimo plėvelės ir juostos')->first();
        $this->assertNotNull($parentCategory);
        $this->assertNull($parentCategory->parent_id);

        $childCategory = Category::where('name', 'Juostos')->first();
        $this->assertNotNull($childCategory);
        $this->assertEquals($parentCategory->id, $childCategory->parent_id);

        $grandchildCategory = Category::where('name', 'Laukui')->first();
        $this->assertNotNull($grandchildCategory);
        $this->assertEquals($childCategory->id, $grandchildCategory->parent_id);
    }

    public function test_category_seeder_creates_multilingual_translations(): void
    {
        // Arrange
        $seeder = new CategorySeeder();

        // Act
        $seeder->run();

        // Assert - Check translations exist for all supported locales
        $category = Category::where('name', 'Sandarinimo plėvelės ir juostos')->first();
        $this->assertNotNull($category);

        $translations = $category->translations;
        $this->assertCount(4, $translations);

        // Check specific translations
        $ltTranslation = $translations->where('locale', 'lt')->first();
        $this->assertNotNull($ltTranslation);
        $this->assertEquals('Sandarinimo plėvelės ir juostos', $ltTranslation->name);

        $enTranslation = $translations->where('locale', 'en')->first();
        $this->assertNotNull($enTranslation);
        $this->assertEquals('Sealing films and tapes', $enTranslation->name);

        $ruTranslation = $translations->where('locale', 'ru')->first();
        $this->assertNotNull($ruTranslation);
        $this->assertEquals('Герметизирующие пленки и ленты', $ruTranslation->name);

        $deTranslation = $translations->where('locale', 'de')->first();
        $this->assertNotNull($deTranslation);
        $this->assertEquals('Dichtungsfolien und Bänder', $deTranslation->name);
    }

    public function test_category_seeder_creates_subcategory_translations(): void
    {
        // Arrange
        $seeder = new CategorySeeder();

        // Act
        $seeder->run();

        // Assert - Check subcategory translations
        $subcategory = Category::where('name', 'Medvarsčiai')->first();
        $this->assertNotNull($subcategory);

        $translations = $subcategory->translations;
        $this->assertCount(4, $translations);

        $enTranslation = $translations->where('locale', 'en')->first();
        $this->assertNotNull($enTranslation);
        $this->assertEquals('Wood screws', $enTranslation->name);

        $ruTranslation = $translations->where('locale', 'ru')->first();
        $this->assertNotNull($ruTranslation);
        $this->assertEquals('Саморезы', $ruTranslation->name);

        $deTranslation = $translations->where('locale', 'de')->first();
        $this->assertNotNull($deTranslation);
        $this->assertEquals('Holzschrauben', $deTranslation->name);
    }

    public function test_category_seeder_creates_deep_hierarchical_structure(): void
    {
        // Arrange
        $seeder = new CategorySeeder();

        // Act
        $seeder->run();

        // Assert - Check 3-level hierarchy
        $level1 = Category::where('name', 'Sandarinimo plėvelės ir juostos')->first();
        $level2 = Category::where('name', 'Juostos')->first();
        $level3 = Category::where('name', 'Laukui')->first();

        $this->assertNotNull($level1);
        $this->assertNotNull($level2);
        $this->assertNotNull($level3);

        $this->assertNull($level1->parent_id);
        $this->assertEquals($level1->id, $level2->parent_id);
        $this->assertEquals($level2->id, $level3->parent_id);

        // Check depth calculation
        $this->assertEquals(1, $level1->level);
        $this->assertEquals(2, $level2->level);
        $this->assertEquals(3, $level3->level);
    }

    public function test_category_seeder_handles_duplicate_categories(): void
    {
        // Arrange
        $seeder = new CategorySeeder();
        $seeder->run();  // First run

        $initialCount = Category::count();
        $initialTranslationCount = CategoryTranslation::count();

        // Act - Run seeder again
        $seeder->run();

        // Assert - No duplicates should be created
        $this->assertEquals($initialCount, Category::count());
        $this->assertEquals($initialTranslationCount, CategoryTranslation::count());
    }

    public function test_category_seeder_creates_all_required_categories(): void
    {
        // Arrange
        $seeder = new CategorySeeder();

        // Act
        $seeder->run();

        // Assert - Check all main categories exist
        $expectedMainCategories = [
            'Sandarinimo plėvelės ir juostos',
            'Tvirtinimo elementai, varžtai, medvarsčiai',
            'Įsukami, įkalami poliai',
            'Chemija statyboms',
            'Įrankiai ir jų priedai',
            'Stogų danga ir priedai',
            'Fasadams',
            'Elektros prekės',
            'Darbo apranga, saugos priemonės',
            'Stogų, grindų, sienų konstrukcijos',
            'Vidaus apdaila',
        ];

        foreach ($expectedMainCategories as $categoryName) {
            $this->assertDatabaseHas('categories', [
                'name' => $categoryName,
                'parent_id' => null,
            ]);
        }
    }

    public function test_category_seeder_creates_categories_with_proper_slugs(): void
    {
        // Arrange
        $seeder = new CategorySeeder();

        // Act
        $seeder->run();

        // Assert - Check slugs are properly generated
        $category = Category::where('name', 'Sandarinimo plėvelės ir juostos')->first();
        $this->assertEquals('sandarinimo-pleveles-ir-juostos', $category->slug);

        $subcategory = Category::where('name', 'Medvarsčiai')->first();
        $this->assertEquals('medvarsciai', $subcategory->slug);

        $deepCategory = Category::where('name', 'Laukui')->first();
        $this->assertEquals('juostos-laukui', $deepCategory->slug);
    }

    public function test_category_seeder_creates_translations_with_proper_slugs(): void
    {
        // Arrange
        $seeder = new CategorySeeder();

        // Act
        $seeder->run();

        // Assert - Check translation slugs
        $category = Category::where('name', 'Sandarinimo plėvelės ir juostos')->first();
        $translations = $category->translations;

        $enTranslation = $translations->where('locale', 'en')->first();
        $this->assertEquals('sealing-films-and-tapes', $enTranslation->slug);

        $ruTranslation = $translations->where('locale', 'ru')->first();
        $this->assertEquals('germetiziruyushchie-plenki-i-lenty', $ruTranslation->slug);

        $deTranslation = $translations->where('locale', 'de')->first();
        $this->assertEquals('abdichtungsfolien-und-baender', $deTranslation->slug);
    }
}
