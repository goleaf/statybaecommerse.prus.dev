<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SeoDataResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(\App\Models\User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_seo_data(): void
    {
        $seoData = SeoData::factory()->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->assertCanSeeTableRecords([$seoData]);
    }

    public function test_can_create_seo_data_for_product(): void
    {
        $product = Product::factory()->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\CreateSeoData::class)
            ->fillForm([
                'seoable_type' => Product::class,
                'seoable_id' => $product->id,
                'locale' => 'lt',
                'title' => 'Test Product SEO Title',
                'description' => 'Test product SEO description for better search engine optimization',
                'keywords' => 'test, product, seo',
                'canonical_url' => 'https://example.com/products/test-product',
                'no_index' => false,
                'no_follow' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('seo_data', [
            'seoable_type' => Product::class,
            'seoable_id' => $product->id,
            'locale' => 'lt',
            'title' => 'Test Product SEO Title',
            'description' => 'Test product SEO description for better search engine optimization',
            'keywords' => 'test, product, seo',
            'canonical_url' => 'https://example.com/products/test-product',
            'no_index' => false,
            'no_follow' => false,
        ]);
    }

    public function test_can_create_seo_data_for_category(): void
    {
        $category = Category::factory()->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\CreateSeoData::class)
            ->fillForm([
                'seoable_type' => Category::class,
                'seoable_id' => $category->id,
                'locale' => 'en',
                'title' => 'Test Category SEO Title',
                'description' => 'Test category SEO description for better search engine optimization',
                'keywords' => 'test, category, seo',
                'canonical_url' => 'https://example.com/categories/test-category',
                'no_index' => false,
                'no_follow' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('seo_data', [
            'seoable_type' => Category::class,
            'seoable_id' => $category->id,
            'locale' => 'en',
            'title' => 'Test Category SEO Title',
            'description' => 'Test category SEO description for better search engine optimization',
            'keywords' => 'test, category, seo',
            'canonical_url' => 'https://example.com/categories/test-category',
            'no_index' => false,
            'no_follow' => false,
        ]);
    }

    public function test_can_create_seo_data_for_brand(): void
    {
        $brand = Brand::factory()->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\CreateSeoData::class)
            ->fillForm([
                'seoable_type' => Brand::class,
                'seoable_id' => $brand->id,
                'locale' => 'lt',
                'title' => 'Test Brand SEO Title',
                'description' => 'Test brand SEO description for better search engine optimization',
                'keywords' => 'test, brand, seo',
                'canonical_url' => 'https://example.com/brands/test-brand',
                'no_index' => false,
                'no_follow' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('seo_data', [
            'seoable_type' => Brand::class,
            'seoable_id' => $brand->id,
            'locale' => 'lt',
            'title' => 'Test Brand SEO Title',
            'description' => 'Test brand SEO description for better search engine optimization',
            'keywords' => 'test, brand, seo',
            'canonical_url' => 'https://example.com/brands/test-brand',
            'no_index' => false,
            'no_follow' => false,
        ]);
    }

    public function test_can_edit_seo_data(): void
    {
        $seoData = SeoData::factory()->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\EditSeoData::class, [
            'record' => $seoData->getRouteKey(),
        ])
            ->fillForm([
                'title' => 'Updated SEO Title',
                'description' => 'Updated SEO description for better search engine optimization',
                'keywords' => 'updated, seo, keywords',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $seoData->refresh();

        $this->assertEquals('Updated SEO Title', $seoData->title);
        $this->assertEquals('Updated SEO description for better search engine optimization', $seoData->description);
        $this->assertEquals('updated, seo, keywords', $seoData->keywords);
    }

    public function test_can_view_seo_data(): void
    {
        $seoData = SeoData::factory()->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ViewSeoData::class, [
            'record' => $seoData->getRouteKey(),
        ])
            ->assertCanSeeText($seoData->title)
            ->assertCanSeeText($seoData->description);
    }

    public function test_can_delete_seo_data(): void
    {
        $seoData = SeoData::factory()->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->callTableAction('delete', $seoData);

        $this->assertDatabaseMissing('seo_data', [
            'id' => $seoData->id,
        ]);
    }

    public function test_can_filter_by_seoable_type(): void
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        $seoData1 = SeoData::factory()->create(['seoable_type' => Product::class, 'seoable_id' => $product->id]);
        $seoData2 = SeoData::factory()->create(['seoable_type' => Category::class, 'seoable_id' => $category->id]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('seoable_type', Product::class)
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_locale(): void
    {
        $seoData1 = SeoData::factory()->create(['locale' => 'lt']);
        $seoData2 = SeoData::factory()->create(['locale' => 'en']);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('locale', 'lt')
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_no_index(): void
    {
        $seoData1 = SeoData::factory()->create(['no_index' => true]);
        $seoData2 = SeoData::factory()->create(['no_index' => false]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('no_index', true)
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_no_follow(): void
    {
        $seoData1 = SeoData::factory()->create(['no_follow' => true]);
        $seoData2 = SeoData::factory()->create(['no_follow' => false]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('no_follow', true)
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_has_title(): void
    {
        $seoData1 = SeoData::factory()->create(['title' => 'Test Title']);
        $seoData2 = SeoData::factory()->create(['title' => null]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('has_title')
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_has_description(): void
    {
        $seoData1 = SeoData::factory()->create(['description' => 'Test Description']);
        $seoData2 = SeoData::factory()->create(['description' => null]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('has_description')
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_has_keywords(): void
    {
        $seoData1 = SeoData::factory()->create(['keywords' => 'test, keywords']);
        $seoData2 = SeoData::factory()->create(['keywords' => null]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('has_keywords')
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_has_canonical_url(): void
    {
        $seoData1 = SeoData::factory()->create(['canonical_url' => 'https://example.com']);
        $seoData2 = SeoData::factory()->create(['canonical_url' => null]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('has_canonical_url')
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_analyze_seo_action(): void
    {
        $seoData = SeoData::factory()->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->callTableAction('analyze_seo', $seoData)
            ->assertNotified('SEO analyzed successfully');
    }

    public function test_can_generate_meta_tags_action(): void
    {
        $seoData = SeoData::factory()->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->callTableAction('generate_meta_tags', $seoData)
            ->assertNotified('Meta tags generated successfully');
    }

    public function test_can_bulk_analyze_seo(): void
    {
        $seoData = SeoData::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->callTableBulkAction('analyze_all_seo', $seoData)
            ->assertNotified('All SEO analyzed successfully');
    }

    public function test_can_bulk_generate_meta_tags(): void
    {
        $seoData = SeoData::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->callTableBulkAction('generate_all_meta_tags', $seoData)
            ->assertNotified('All meta tags generated successfully');
    }

    public function test_can_search_seo_data(): void
    {
        $seoData1 = SeoData::factory()->create(['title' => 'Test SEO Title']);
        $seoData2 = SeoData::factory()->create(['title' => 'Another Title']);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->searchTable('Test')
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_sort_seo_data(): void
    {
        $seoData1 = SeoData::factory()->create(['title' => 'A SEO Title']);
        $seoData2 = SeoData::factory()->create(['title' => 'B SEO Title']);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->sortTable('title')
            ->assertCanSeeTableRecords([$seoData1, $seoData2], inOrder: true);
    }

    public function test_form_validation_works(): void
    {
        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\CreateSeoData::class)
            ->fillForm([
                'seoable_type' => '',  // Required field
                'seoable_id' => '',  // Required field
                'locale' => '',  // Required field
                'title' => '',  // Required field
                'description' => '',  // Required field
                'canonical_url' => 'invalid-url',  // Must be valid URL
            ])
            ->call('create')
            ->assertHasFormErrors(['seoable_type', 'seoable_id', 'locale', 'title', 'description', 'canonical_url']);
    }

    public function test_relationships_are_loaded(): void
    {
        $product = Product::factory()->create();
        $seoData = SeoData::factory()->create([
            'seoable_type' => Product::class,
            'seoable_id' => $product->id,
        ]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ViewSeoData::class, [
            'record' => $seoData->getRouteKey(),
        ])
            ->assertCanSeeText($product->name);
    }

    public function test_seo_score_is_displayed_correctly(): void
    {
        $seoData = SeoData::factory()->create([
            'title' => 'Test Title',
            'description' => 'Test description',
            'keywords' => 'test, keywords',
            'canonical_url' => 'https://example.com',
        ]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ViewSeoData::class, [
            'record' => $seoData->getRouteKey(),
        ])
            ->assertCanSeeText('%');  // SEO score percentage
    }

    public function test_robots_display_is_correct(): void
    {
        $seoData1 = SeoData::factory()->create(['no_index' => false, 'no_follow' => false]);
        $seoData2 = SeoData::factory()->create(['no_index' => true, 'no_follow' => true]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ViewSeoData::class, [
            'record' => $seoData1->getRouteKey(),
        ])
            ->assertCanSeeText('index, follow');

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ViewSeoData::class, [
            'record' => $seoData2->getRouteKey(),
        ])
            ->assertCanSeeText('noindex, nofollow');
    }
}
