<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class SystemSettingCategoryTranslationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Ensure required permissions exist and are assigned to avoid Filament navigation policy failures
        Permission::findOrCreate('view notifications', 'web');
        $this->adminUser->givePermissionTo('view notifications');

        // Stub missing Filament system-settings routes referenced by navigation/topbar
        Route::middleware('web')->group(function (): void {
            Route::get('/_test/system-settings', fn () => response('ok'))->name('filament.admin.resources.system-settings.index');
            Route::get('/_test/system-settings/create', fn () => response('ok'))->name('filament.admin.resources.system-settings.create');
            Route::get('/_test/system-settings/{record}', fn () => response('ok'))->name('filament.admin.resources.system-settings.view');
            Route::get('/_test/system-settings/{record}/edit', fn () => response('ok'))->name('filament.admin.resources.system-settings.edit');
        });

        $this->actingAs($this->adminUser);
    }

    public function test_can_list_system_setting_category_translations(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();
        $translations = SystemSettingCategoryTranslation::factory(3)
            ->forCategory($category)
            ->create();

        // Act & Assert
        $this
            ->get('/admin/system-setting-category-translations')
            ->assertOk()
            ->assertSee($translations->first()->name);
    }

    public function test_can_create_system_setting_category_translation(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();

        $translationData = [
            'system_setting_category_id' => $category->id,
            'locale' => 'lt',
            'name' => 'Lietuviškas pavadinimas',
            'description' => 'Lietuviškas aprašymas',
        ];

        // Act
        $this->post('/admin/system-setting-category-translations', $translationData);

        // Assert
        $this->assertDatabaseHas('system_setting_category_translations', $translationData);
    }

    public function test_can_view_system_setting_category_translation(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();
        $translation = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->create();

        // Act & Assert
        $this
            ->get("/admin/system-setting-category-translations/{$translation->id}")
            ->assertOk()
            ->assertSee($translation->name)
            ->assertSee($translation->description);
    }

    public function test_can_edit_system_setting_category_translation(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();
        $translation = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->create();

        $updatedData = [
            'name' => 'Atnaujintas pavadinimas',
            'description' => 'Atnaujintas aprašymas',
        ];

        // Act
        $this->put("/admin/system-setting-category-translations/{$translation->id}", $updatedData);

        // Assert
        $this->assertDatabaseHas('system_setting_category_translations', [
            'id' => $translation->id,
            'name' => $updatedData['name'],
            'description' => $updatedData['description'],
        ]);
    }

    public function test_can_delete_system_setting_category_translation(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();
        $translation = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->create();

        // Act
        $this->delete("/admin/system-setting-category-translations/{$translation->id}");

        // Assert
        $this->assertDatabaseMissing('system_setting_category_translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_can_duplicate_system_setting_category_translation(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();
        $translation = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->create();

        // Act
        $response = $this->post("/admin/system-setting-category-translations/{$translation->id}/duplicate");

        // Assert
        $this->assertDatabaseHas('system_setting_category_translations', [
            'system_setting_category_id' => $category->id,
            'locale' => $translation->locale,
            'name' => $translation->name.' (Copy)',
        ]);
    }

    public function test_can_filter_by_category(): void
    {
        // Arrange
        $category1 = SystemSettingCategory::factory()->create();
        $category2 = SystemSettingCategory::factory()->create();

        $translation1 = SystemSettingCategoryTranslation::factory()
            ->forCategory($category1)
            ->create();

        $translation2 = SystemSettingCategoryTranslation::factory()
            ->forCategory($category2)
            ->create();

        // Act & Assert
        $this
            ->get("/admin/system-setting-category-translations?filter[system_setting_category_id]={$category1->id}")
            ->assertOk()
            ->assertSee($translation1->name)
            ->assertDontSee($translation2->name);
    }

    public function test_can_filter_by_locale(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();

        $lithuanianTranslation = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->lithuanian()
            ->create();

        $englishTranslation = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->english()
            ->create();

        // Act & Assert
        $this
            ->get('/admin/system-setting-category-translations?filter[locale]=lt')
            ->assertOk()
            ->assertSee($lithuanianTranslation->name)
            ->assertDontSee($englishTranslation->name);
    }

    public function test_can_search_by_name(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();

        $translation1 = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->create(['name' => 'Unique Name 123']);

        $translation2 = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->create(['name' => 'Different Name']);

        // Act & Assert
        $this
            ->get('/admin/system-setting-category-translations?search=Unique')
            ->assertOk()
            ->assertSee($translation1->name)
            ->assertDontSee($translation2->name);
    }

    public function test_can_bulk_delete_translations(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();
        $translations = SystemSettingCategoryTranslation::factory(3)
            ->forCategory($category)
            ->create();

        $translationIds = $translations->pluck('id')->toArray();

        // Act
        $this->post('/admin/system-setting-category-translations/bulk-delete', [
            'selectedItems' => $translationIds,
        ]);

        // Assert
        foreach ($translationIds as $id) {
            $this->assertDatabaseMissing('system_setting_category_translations', [
                'id' => $id,
            ]);
        }
    }

    public function test_can_export_translations(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();
        $translations = SystemSettingCategoryTranslation::factory(3)
            ->forCategory($category)
            ->create();

        $translationIds = $translations->pluck('id')->toArray();

        // Act & Assert
        $response = $this->post('/admin/system-setting-category-translations/bulk-export', [
            'selectedItems' => $translationIds,
        ]);

        $response->assertOk();
    }

    public function test_validation_requires_category(): void
    {
        // Arrange
        $translationData = [
            'locale' => 'lt',
            'name' => 'Test Name',
            'description' => 'Test Description',
        ];

        // Act & Assert
        $response = $this->post('/admin/system-setting-category-translations', $translationData);

        $response->assertSessionHasErrors(['system_setting_category_id']);
    }

    public function test_validation_requires_locale(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();

        $translationData = [
            'system_setting_category_id' => $category->id,
            'name' => 'Test Name',
            'description' => 'Test Description',
        ];

        // Act & Assert
        $response = $this->post('/admin/system-setting-category-translations', $translationData);

        $response->assertSessionHasErrors(['locale']);
    }

    public function test_validation_requires_name(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();

        $translationData = [
            'system_setting_category_id' => $category->id,
            'locale' => 'lt',
            'description' => 'Test Description',
        ];

        // Act & Assert
        $response = $this->post('/admin/system-setting-category-translations', $translationData);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_validation_locale_must_be_unique_per_category(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();

        SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->lithuanian()
            ->create();

        $translationData = [
            'system_setting_category_id' => $category->id,
            'locale' => 'lt',
            'name' => 'Another Name',
            'description' => 'Another Description',
        ];

        // Act & Assert
        $response = $this->post('/admin/system-setting-category-translations', $translationData);

        $response->assertSessionHasErrors(['locale']);
    }

    public function test_can_create_translation_with_create_option_form(): void
    {
        // Arrange
        $categoryData = [
            'name' => 'New Category',
            'slug' => 'new-category',
            'description' => 'New Category Description',
        ];

        $translationData = [
            'system_setting_category_id' => null,  // Will be created via form
            'locale' => 'lt',
            'name' => 'Lietuviškas pavadinimas',
            'description' => 'Lietuviškas aprašymas',
        ];

        // Act
        $response = $this->post('/admin/system-setting-category-translations', array_merge($translationData, $categoryData));

        // Assert
        $this->assertDatabaseHas('system_setting_categories', [
            'name' => $categoryData['name'],
            'slug' => $categoryData['slug'],
        ]);

        $category = SystemSettingCategory::where('slug', $categoryData['slug'])->first();
        $this->assertDatabaseHas('system_setting_category_translations', [
            'system_setting_category_id' => $category->id,
            'locale' => $translationData['locale'],
            'name' => $translationData['name'],
        ]);
    }

    public function test_can_sort_by_locale(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();

        $englishTranslation = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->english()
            ->create();

        $lithuanianTranslation = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->lithuanian()
            ->create();

        // Act & Assert
        $response = $this->get('/admin/system-setting-category-translations?sort=locale');

        $response->assertOk();
        // English should come before Lithuanian alphabetically
    }

    public function test_can_sort_by_name(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();

        $translation1 = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->create(['name' => 'Zebra Name']);

        $translation2 = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->create(['name' => 'Apple Name']);

        // Act & Assert
        $response = $this->get('/admin/system-setting-category-translations?sort=name');

        $response->assertOk();
    }

    public function test_can_sort_by_created_at(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();

        $translation1 = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->create(['created_at' => now()->subDay()]);

        $translation2 = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->create(['created_at' => now()]);

        // Act & Assert
        $response = $this->get('/admin/system-setting-category-translations?sort=-created_at');

        $response->assertOk();
    }

    public function test_locale_badge_colors(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();

        $englishTranslation = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->english()
            ->create();

        $lithuanianTranslation = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->lithuanian()
            ->create();

        // Act & Assert
        $this
            ->get('/admin/system-setting-category-translations')
            ->assertOk()
            ->assertSee($englishTranslation->locale)
            ->assertSee($lithuanianTranslation->locale);
    }

    public function test_tooltip_shows_full_content_for_long_text(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();
        $longName = str_repeat('Very Long Name ', 10);  // Make it longer than 50 chars
        $longDescription = str_repeat('Very Long Description ', 20);  // Make it longer than 50 chars

        $translation = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->create([
                'name' => $longName,
                'description' => $longDescription,
            ]);

        // Act & Assert
        $this
            ->get('/admin/system-setting-category-translations')
            ->assertOk()
            ->assertSee($translation->name)
            ->assertSee($translation->description);
    }

    public function test_can_toggle_created_at_column(): void
    {
        // Arrange
        $category = SystemSettingCategory::factory()->create();
        $translation = SystemSettingCategoryTranslation::factory()
            ->forCategory($category)
            ->create();

        // Act & Assert
        $response = $this->get('/admin/system-setting-category-translations');
        $response->assertOk();

        // Toggle the column
        $response = $this->get('/admin/system-setting-category-translations?toggle=created_at');
        $response->assertOk();
    }
}
