<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\NewsImageResource\Pages\ListNewsImages as NewsImageResourceListNewsImages;
use App\Filament\Resources\NewsImages\Pages\ListNewsImages as LegacyNewsImagesListNewsImages;
use App\Filament\Resources\NewsTagResource\Pages\ListNewsTags as NewsTagResourceListNewsTags;
use App\Filament\Resources\NewsTags\Pages\ListNewsTags as LegacyNewsTagsListNewsTags;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigResourceSimples;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigSimples;
use App\Filament\Resources\SystemSettingCategories\Pages\ListSystemSettingCategories as LegacyListSystemSettingCategories;
use App\Filament\Resources\SystemSettingCategoryResource\Pages\ListSystemSettingCategories;
use App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages\ListSystemSettingCategoryTranslations;
use App\Filament\Resources\UserWishlistResource\Pages\ListUserWishlists;
use App\Models\NewsImage;
use App\Models\NewsTag;
use App\Models\RecommendationConfigSimple;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use App\Models\User;
use App\Models\UserWishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Additional Filament list-page smoke coverage for resources that previously lacked Livewire assertions.
 */
final class AdditionalFilamentResourceCoverageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament resolves the admin panel before mounting Livewire pages.
        $this->resolveAdminPanel();

        // Normalise locale-dependent factories so seeded records expose English strings.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate as an administrator so resource policies permit table access.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    /**
     * @return array<string, array{class-string, string}>
     */
    public static function resourceProvider(): array
    {
        // Map each Filament list page to the helper that seeds a representative record.
        return [
            'news image legacy listings'            => [LegacyNewsImagesListNewsImages::class, 'createNewsImageRecord'],
            'news image listings'                   => [NewsImageResourceListNewsImages::class, 'createNewsImageRecord'],
            'news tag legacy listings'              => [LegacyNewsTagsListNewsTags::class, 'createNewsTagRecord'],
            'news tag listings'                     => [NewsTagResourceListNewsTags::class, 'createNewsTagRecord'],
            'recommendation config simple legacy'   => [ListRecommendationConfigResourceSimples::class, 'createRecommendationConfigSimpleRecord'],
            'recommendation config simple listings' => [ListRecommendationConfigSimples::class, 'createRecommendationConfigSimpleRecord'],
            'system setting categories legacy'      => [LegacyListSystemSettingCategories::class, 'createSystemSettingCategoryRecord'],
            'system setting categories'             => [ListSystemSettingCategories::class, 'createSystemSettingCategoryRecord'],
            'system setting category translations'  => [ListSystemSettingCategoryTranslations::class, 'createSystemSettingCategoryTranslationRecord'],
            'user wishlists'                        => [ListUserWishlists::class, 'createUserWishlistRecord'],
        ];
    }

    #[DataProvider('resourceProvider')]
    public function test_list_pages_render_seeded_records(string $pageClass, string $factoryMethod): void
    {
        // Seed a record using the dedicated helper so the table has data to render.
        $record = $this->{$factoryMethod}();

        // Hydrate the table state prior to asserting that the seeded record is visible.
        Livewire::test($pageClass)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$record]);
    }

    private function createNewsImageRecord(): NewsImage
    {
        // Generate a deterministic news image so both legacy and modern resources can surface the asset.
        return NewsImage::factory()->create([
            'file_path' => 'news-images/coverage-image.jpg',
            'alt_text'  => 'Coverage news image',
        ]);
    }

    private function createNewsTagRecord(): NewsTag
    {
        // Persist a bilingual news tag ensuring translations hydrate consistently across namespace variants.
        return NewsTag::factory()->create([
            'name' => 'Coverage Tag',
            'slug' => 'coverage-tag',
        ]);
    }

    private function createRecommendationConfigSimpleRecord(): RecommendationConfigSimple
    {
        // Store an active recommendation configuration so list filters expose a realistic payload.
        return RecommendationConfigSimple::factory()->create([
            'name'     => 'Coverage Config Simple',
            'code'     => 'coverage-config',
            'is_active'=> true,
        ]);
    }

    private function createSystemSettingCategoryRecord(): SystemSettingCategory
    {
        // Create a root system setting category to give the administration listings visible context.
        return SystemSettingCategory::factory()->create([
            'name' => 'Coverage Settings',
            'slug' => 'coverage-settings',
        ]);
    }

    private function createSystemSettingCategoryTranslationRecord(): SystemSettingCategoryTranslation
    {
        // Attach an English translation to the coverage category so localization tables load without errors.
        $category = $this->createSystemSettingCategoryRecord();

        return SystemSettingCategoryTranslation::factory()->for($category)->english()->create([
            'name'        => 'Coverage Settings EN',
            'description' => 'Coverage category description',
        ]);
    }

    private function createUserWishlistRecord(): UserWishlist
    {
        // Provision a public wishlist belonging to the administrator to keep engagement dashboards populated.
        return UserWishlist::factory()->public()->for($this->admin)->create([
            'name' => 'Coverage Wishlist',
        ]);
    }
}
