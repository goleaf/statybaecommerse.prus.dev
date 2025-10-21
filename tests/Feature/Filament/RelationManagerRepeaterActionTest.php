<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\RelationManagers\TranslationsRelationManager;
use App\Models\Category;
use App\Models\Translations\CategoryTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class RelationManagerRepeaterActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_translations_can_be_bulk_managed_via_repeater(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $ltTranslation = CategoryTranslation::factory()
            ->forCategory($category)
            ->forLocale('lt')
            ->create([
                'name'              => 'Original LT',
                'slug'              => 'original-lt',
                'description'       => 'Original LT description',
                'short_description' => 'Original LT short',
                'seo_title'         => 'Original LT SEO',
                'seo_description'   => 'Original LT SEO description',
            ]);

        $enTranslation = CategoryTranslation::factory()
            ->forCategory($category)
            ->forLocale('en')
            ->create([
                'name'              => 'Original EN',
                'slug'              => 'original-en',
                'description'       => 'Original EN description',
                'short_description' => 'Original EN short',
                'seo_title'         => 'Original EN SEO',
                'seo_description'   => 'Original EN SEO description',
            ]);

        $this->actingAs($admin);

        Livewire::test(TranslationsRelationManager::class, [
            'ownerRecord' => $category,
            'pageClass'   => EditCategory::class,
        ])->callTableAction(
            'edit-relationship',
            null,
            [
                'translations' => [
                    [
                        'id'                => $ltTranslation->id,
                        'locale'            => 'lt',
                        'name'              => 'Updated LT',
                        'slug'              => 'updated-lt',
                        'description'       => 'Updated LT description',
                        'short_description' => 'Updated LT short',
                        'seo_title'         => 'Updated LT SEO',
                        'seo_description'   => 'Updated LT SEO description',
                    ],
                    [
                        'id'                => null,
                        'locale'            => 'en',
                        'name'              => 'New EN',
                        'slug'              => 'new-en',
                        'description'       => 'New EN description',
                        'short_description' => 'New EN short',
                        'seo_title'         => 'New EN SEO',
                        'seo_description'   => 'New EN SEO description',
                    ],
                ],
            ]
        )->assertHasNoFormErrors();

        $this->assertDatabaseHas('category_translations', [
            'id'   => $ltTranslation->id,
            'name' => 'Updated LT',
            'slug' => 'updated-lt',
        ]);

        $this->assertDatabaseMissing('category_translations', [
            'id' => $enTranslation->id,
        ]);

        $this->assertDatabaseHas('category_translations', [
            'category_id' => $category->id,
            'locale'      => 'en',
            'name'        => 'New EN',
            'slug'        => 'new-en',
        ]);

        $this->assertSame(2, CategoryTranslation::where('category_id', $category->id)->count());
    }
}
