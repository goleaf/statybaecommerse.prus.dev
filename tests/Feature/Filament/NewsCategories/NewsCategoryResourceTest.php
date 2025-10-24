<?php declare(strict_types=1);

namespace Tests\Feature\Filament\NewsCategories;

use App\Filament\Resources\NewsCategories\Pages\CreateNewsCategory;
use App\Filament\Resources\NewsCategories\Pages\EditNewsCategory;
use App\Filament\Resources\NewsCategories\Pages\ListNewsCategories;
use App\Models\NewsCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class NewsCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($admin);
    }

    public function test_list_page_displays_categories(): void
    {
        $categories = collect([
            $this->createCategoryWithTranslations(['sort_order' => 1]),
            $this->createCategoryWithTranslations(['sort_order' => 2]),
        ]);

        Livewire::test(ListNewsCategories::class)
            ->assertCanSeeTableRecords($categories)
            ->assertCanSeeTableColumns([
                'name',
                'slug',
                'is_visible',
                'color',
                'icon',
            ]);
    }

    public function test_create_form_contains_metadata_fields(): void
    {
        Livewire::test(CreateNewsCategory::class)
            ->assertFormFieldExists('is_visible')
            ->assertFormFieldExists('parent_id')
            ->assertFormFieldExists('sort_order')
            ->assertFormFieldExists('color')
            ->assertFormFieldExists('icon');
    }

    public function test_edit_form_updates_metadata_and_allows_hidden_parent(): void
    {
        $hiddenParent = $this->createCategoryWithTranslations([
            'is_visible' => false,
            'sort_order' => 5,
        ]);

        $category = $this->createCategoryWithTranslations([
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        Livewire::test(EditNewsCategory::class, [
            'record' => $category->slug,
        ])
            ->fillForm([
                'is_visible' => false,
                'parent_id' => $hiddenParent->slug,
                'sort_order' => 10,
                'color' => '#123456',
                'icon' => 'heroicon-o-newspaper',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $category->refresh();

        $this->assertFalse($category->is_visible);
        $this->assertSame($hiddenParent->getKey(), $category->parent_id);
        $this->assertSame(10, $category->sort_order);
        $this->assertSame('#123456', $category->color);
        $this->assertSame('heroicon-o-newspaper', $category->icon);
    }

    private function createCategoryWithTranslations(array $attributes = []): NewsCategory
    {
        $category = NewsCategory::factory()->create($attributes);

        $category->translations()->createMany([
            [
                'locale' => 'lt',
                'name' => 'Paslėpta kategorija',
                'slug' => "paslepta-kategorija-{$category->slug}",
                'description' => 'Kategorija lietuvių kalba',
            ],
            [
                'locale' => 'en',
                'name' => 'Hidden Category',
                'slug' => "hidden-category-{$category->slug}",
                'description' => 'Category in English',
            ],
        ]);

        return $category->fresh();
    }
}
