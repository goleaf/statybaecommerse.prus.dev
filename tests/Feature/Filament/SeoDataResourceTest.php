<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\SeoData;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SeoDataResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_can_list_seo_data(): void
    {
        SeoData::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->assertCanSeeTableRecords(SeoData::all());
    }

    public function test_can_create_seo_data(): void
    {
        $product = Product::factory()->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\CreateSeoData::class)
            ->fillForm([
                'seoable_type' => Product::class,
                'seoable_id' => $product->id,
                'locale' => 'lt',
                'title' => 'Test SEO Title',
                'description' => 'Test SEO description for the page',
                'keywords' => 'test, seo, keywords',
                'canonical_url' => 'https://example.com/test-page',
                'no_index' => false,
                'no_follow' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('seo_data', [
            'seoable_type' => Product::class,
            'seoable_id' => $product->id,
            'locale' => 'lt',
            'title' => 'Test SEO Title',
            'description' => 'Test SEO description for the page',
            'keywords' => 'test, seo, keywords',
            'canonical_url' => 'https://example.com/test-page',
            'no_index' => false,
            'no_follow' => false,
        ]);
    }

    public function test_can_edit_seo_data(): void
    {
        $seoData = SeoData::factory()->create([
            'title' => 'Original Title',
            'description' => 'Original Description',
        ]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\EditSeoData::class, [
            'record' => $seoData->getRouteKey(),
        ])
            ->fillForm([
                'title' => 'Updated Title',
                'description' => 'Updated Description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('seo_data', [
            'id' => $seoData->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ]);
    }

    public function test_can_view_seo_data(): void
    {
        $seoData = SeoData::factory()->create();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ViewSeoData::class, [
            'record' => $seoData->getRouteKey(),
        ])
            ->assertCanSeeTableRecords([$seoData]);
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

    public function test_can_filter_seo_data_by_locale(): void
    {
        SeoData::factory()->create(['locale' => 'lt']);
        SeoData::factory()->create(['locale' => 'en']);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('locale', 'lt')
            ->assertCanSeeTableRecords(SeoData::where('locale', 'lt')->get())
            ->assertCanNotSeeTableRecords(SeoData::where('locale', 'en')->get());
    }

    public function test_can_filter_seo_data_by_type(): void
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();

        SeoData::factory()->create([
            'seoable_type' => Product::class,
            'seoable_id' => $product->id,
        ]);
        SeoData::factory()->create([
            'seoable_type' => Category::class,
            'seoable_id' => $category->id,
        ]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('seoable_type', Product::class)
            ->assertCanSeeTableRecords(SeoData::where('seoable_type', Product::class)->get())
            ->assertCanNotSeeTableRecords(SeoData::where('seoable_type', Category::class)->get());
    }

    public function test_can_filter_seo_data_by_has_title(): void
    {
        SeoData::factory()->create(['title' => 'Test Title']);
        SeoData::factory()->create(['title' => null]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('has_title', true)
            ->assertCanSeeTableRecords(SeoData::whereNotNull('title')->get())
            ->assertCanNotSeeTableRecords(SeoData::whereNull('title')->get());
    }

    public function test_can_filter_seo_data_by_has_description(): void
    {
        SeoData::factory()->create(['description' => 'Test Description']);
        SeoData::factory()->create(['description' => null]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('has_description', true)
            ->assertCanSeeTableRecords(SeoData::whereNotNull('description')->get())
            ->assertCanNotSeeTableRecords(SeoData::whereNull('description')->get());
    }

    public function test_can_filter_seo_data_by_has_keywords(): void
    {
        SeoData::factory()->create(['keywords' => 'test, keywords']);
        SeoData::factory()->create(['keywords' => null]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('has_keywords', true)
            ->assertCanSeeTableRecords(SeoData::whereNotNull('keywords')->get())
            ->assertCanNotSeeTableRecords(SeoData::whereNull('keywords')->get());
    }

    public function test_can_filter_seo_data_by_has_canonical_url(): void
    {
        SeoData::factory()->create(['canonical_url' => 'https://example.com']);
        SeoData::factory()->create(['canonical_url' => null]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('has_canonical_url', true)
            ->assertCanSeeTableRecords(SeoData::whereNotNull('canonical_url')->get())
            ->assertCanNotSeeTableRecords(SeoData::whereNull('canonical_url')->get());
    }

    public function test_can_filter_seo_data_by_has_structured_data(): void
    {
        SeoData::factory()->create(['structured_data' => ['@context' => 'https://schema.org']]);
        SeoData::factory()->create(['structured_data' => null]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('has_structured_data', true)
            ->assertCanSeeTableRecords(SeoData::whereNotNull('structured_data')->get())
            ->assertCanNotSeeTableRecords(SeoData::whereNull('structured_data')->get());
    }

    public function test_can_filter_seo_data_by_no_index(): void
    {
        SeoData::factory()->create(['no_index' => true]);
        SeoData::factory()->create(['no_index' => false]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('no_index', true)
            ->assertCanSeeTableRecords(SeoData::where('no_index', true)->get())
            ->assertCanNotSeeTableRecords(SeoData::where('no_index', false)->get());
    }

    public function test_can_filter_seo_data_by_no_follow(): void
    {
        SeoData::factory()->create(['no_follow' => true]);
        SeoData::factory()->create(['no_follow' => false]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('no_follow', true)
            ->assertCanSeeTableRecords(SeoData::where('no_follow', true)->get())
            ->assertCanNotSeeTableRecords(SeoData::where('no_follow', false)->get());
    }

    public function test_can_search_seo_data(): void
    {
        SeoData::factory()->create(['title' => 'Test Product SEO']);
        SeoData::factory()->create(['title' => 'Test Category SEO']);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->searchTable('Product')
            ->assertCanSeeTableRecords(SeoData::where('title', 'like', '%Product%')->get())
            ->assertCanNotSeeTableRecords(SeoData::where('title', 'like', '%Category%')->get());
    }

    public function test_can_sort_seo_data_by_title(): void
    {
        SeoData::factory()->create(['title' => 'Z Title']);
        SeoData::factory()->create(['title' => 'A Title']);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->sortTable('title')
            ->assertCanSeeTableRecords(SeoData::orderBy('title')->get());
    }

    public function test_can_sort_seo_data_by_created_at(): void
    {
        $oldSeoData = SeoData::factory()->create(['created_at' => now()->subDay()]);
        $newSeoData = SeoData::factory()->create(['created_at' => now()]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords(SeoData::orderBy('created_at', 'desc')->get());
    }

    public function test_can_use_products_tab(): void
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();

        SeoData::factory()->create([
            'seoable_type' => Product::class,
            'seoable_id' => $product->id,
        ]);
        SeoData::factory()->create([
            'seoable_type' => Category::class,
            'seoable_id' => $category->id,
        ]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->assertCanSeeTableRecords(SeoData::all())
            ->assertCanSeeTableTabs(['all', 'products', 'categories', 'brands', 'lithuanian', 'english', 'needs_optimization', 'excellent_seo']);
    }

    public function test_can_use_categories_tab(): void
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();

        SeoData::factory()->create([
            'seoable_type' => Product::class,
            'seoable_id' => $product->id,
        ]);
        SeoData::factory()->create([
            'seoable_type' => Category::class,
            'seoable_id' => $category->id,
        ]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->assertCanSeeTableTabs(['all', 'products', 'categories', 'brands', 'lithuanian', 'english', 'needs_optimization', 'excellent_seo']);
    }

    public function test_can_use_brands_tab(): void
    {
        $product = Product::factory()->create();
        $brand = Brand::factory()->create();

        SeoData::factory()->create([
            'seoable_type' => Product::class,
            'seoable_id' => $product->id,
        ]);
        SeoData::factory()->create([
            'seoable_type' => Brand::class,
            'seoable_id' => $brand->id,
        ]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->assertCanSeeTableTabs(['all', 'products', 'categories', 'brands', 'lithuanian', 'english', 'needs_optimization', 'excellent_seo']);
    }

    public function test_can_use_lithuanian_tab(): void
    {
        SeoData::factory()->create(['locale' => 'lt']);
        SeoData::factory()->create(['locale' => 'en']);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->assertCanSeeTableTabs(['all', 'products', 'categories', 'brands', 'lithuanian', 'english', 'needs_optimization', 'excellent_seo']);
    }

    public function test_can_use_english_tab(): void
    {
        SeoData::factory()->create(['locale' => 'lt']);
        SeoData::factory()->create(['locale' => 'en']);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->assertCanSeeTableTabs(['all', 'products', 'categories', 'brands', 'lithuanian', 'english', 'needs_optimization', 'excellent_seo']);
    }

    public function test_can_use_needs_optimization_tab(): void
    {
        SeoData::factory()->create([
            'title' => 'Test Title',
            'description' => 'Test Description',
            'keywords' => 'test, keywords',
            'canonical_url' => 'https://example.com',
            'structured_data' => ['@context' => 'https://schema.org'],
        ]);
        SeoData::factory()->create([
            'title' => null,
            'description' => null,
            'keywords' => null,
            'canonical_url' => null,
            'structured_data' => null,
        ]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->assertCanSeeTableTabs(['all', 'products', 'categories', 'brands', 'lithuanian', 'english', 'needs_optimization', 'excellent_seo']);
    }

    public function test_can_use_excellent_seo_tab(): void
    {
        SeoData::factory()->create([
            'title' => 'Test Title',
            'description' => 'Test Description',
            'keywords' => 'test, keywords',
            'canonical_url' => 'https://example.com',
            'structured_data' => ['@context' => 'https://schema.org'],
            'no_index' => false,
            'no_follow' => false,
        ]);
        SeoData::factory()->create([
            'title' => null,
            'description' => null,
            'keywords' => null,
            'canonical_url' => null,
            'structured_data' => null,
        ]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->assertCanSeeTableTabs(['all', 'products', 'categories', 'brands', 'lithuanian', 'english', 'needs_optimization', 'excellent_seo']);
    }
}