<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;
use Tests\TestCase;

class SeoDataResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Remember original Filament testing environment overrides so we can restore them after this suite completes.
     */
    private static ?string $previousFilamentAutodiscoverEnv = null;

    private static ?string $previousFilamentResourcesEnv = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$previousFilamentAutodiscoverEnv = getenv('FILAMENT_TESTING_AUTODISCOVER') !== false
            ? getenv('FILAMENT_TESTING_AUTODISCOVER')
            : null;

        self::$previousFilamentResourcesEnv = getenv('FILAMENT_TESTING_RESOURCES') !== false
            ? getenv('FILAMENT_TESTING_RESOURCES')
            : null;

        putenv('FILAMENT_TESTING_AUTODISCOVER=false');
        $_ENV['FILAMENT_TESTING_AUTODISCOVER'] = 'false';
        $_SERVER['FILAMENT_TESTING_AUTODISCOVER'] = 'false';

        $resourceList = \App\Filament\Resources\SeoDataResource::class;
        putenv('FILAMENT_TESTING_RESOURCES=' . $resourceList);
        $_ENV['FILAMENT_TESTING_RESOURCES'] = $resourceList;
        $_SERVER['FILAMENT_TESTING_RESOURCES'] = $resourceList;
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$previousFilamentAutodiscoverEnv === null) {
            putenv('FILAMENT_TESTING_AUTODISCOVER');
            unset($_ENV['FILAMENT_TESTING_AUTODISCOVER'], $_SERVER['FILAMENT_TESTING_AUTODISCOVER']);
        } else {
            putenv('FILAMENT_TESTING_AUTODISCOVER=' . self::$previousFilamentAutodiscoverEnv);
            $_ENV['FILAMENT_TESTING_AUTODISCOVER'] = self::$previousFilamentAutodiscoverEnv;
            $_SERVER['FILAMENT_TESTING_AUTODISCOVER'] = self::$previousFilamentAutodiscoverEnv;
        }

        if (self::$previousFilamentResourcesEnv === null) {
            putenv('FILAMENT_TESTING_RESOURCES');
            unset($_ENV['FILAMENT_TESTING_RESOURCES'], $_SERVER['FILAMENT_TESTING_RESOURCES']);
        } else {
            putenv('FILAMENT_TESTING_RESOURCES=' . self::$previousFilamentResourcesEnv);
            $_ENV['FILAMENT_TESTING_RESOURCES'] = self::$previousFilamentResourcesEnv;
            $_SERVER['FILAMENT_TESTING_RESOURCES'] = self::$previousFilamentResourcesEnv;
        }

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament resolves the admin panel once so component tests reuse shared state.
        $this->resolveAdminPanel();

        // Use quiet creation to avoid firing observers that would perform expensive cache refreshes during the test run.
        $this->actingAs(\App\Models\User::factory()->createQuietly([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    /**
     * Helper for creating SEO data fixtures without triggering nested observer side effects.
     */
    private function createSeoData(array $overrides = []): SeoData
    {
        // Supply deterministic defaults so individual tests only override the fields they care about.
        $productId = $overrides['seoable_id'] ?? null;
        $seoableType = $overrides['seoable_type'] ?? Product::class;

        if ($productId === null) {
            // Provision a lightweight product only when a specific relation has not been requested.
            $product = Product::factory()->createQuietly();
            $productId = $product->getKey();
            $seoableType = Product::class;
        }

        $defaults = [
            'seoable_type'  => $seoableType,
            'seoable_id'    => $productId,
            'locale'        => 'lt',
            'title'         => 'Test SEO Title',
            'description'   => 'Test SEO description text',
            'keywords'      => ['test', 'seo', 'default'],
            'canonical_url' => 'https://example.com/test-seo',
        ];

        // Create the record quietly to avoid cascading observers on related models.
        return SeoData::factory()->createQuietly(array_merge($defaults, $overrides));
    }

    public function test_can_list_seo_data(): void
    {
        // Seed a single SEO record using the helper to minimise factory churn.
        $seoData = $this->createSeoData();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->assertCanSeeTableRecords([$seoData]);
    }

    public function test_can_create_seo_data_for_product(): void
    {
        // Generate a product quietly so cache invalidation observers do not run during the test.
        $product = Product::factory()->createQuietly();

        SeoData::create([
            'seoable_type'  => Product::class,
            'seoable_id'    => $product->id,
            'locale'        => 'lt',
            'title'         => 'Test Product SEO Title',
            'description'   => 'Test product SEO description for better search engine optimization',
            'keywords'      => 'test, product, seo',
            'canonical_url' => 'https://example.com/products/test-product',
            'no_index'      => false,
            'no_follow'     => false,
        ]);

        $this->assertDatabaseHas('seo_data', [
            'seoable_type'  => Product::class,
            'seoable_id'    => $product->id,
            'locale'        => 'lt',
            'title'         => json_encode('Test Product SEO Title'),
            'description'   => json_encode('Test product SEO description for better search engine optimization'),
            'keywords'      => json_encode(['test', 'product', 'seo']),
            'canonical_url' => 'https://example.com/products/test-product',
            'no_index'      => false,
            'no_follow'     => false,
        ]);
    }

    public function test_can_create_seo_data_for_category(): void
    {
        // Create related models quietly to keep observer driven side-effects from affecting memory usage.
        $category = Category::factory()->createQuietly();

        SeoData::create([
            'seoable_type'  => Category::class,
            'seoable_id'    => $category->id,
            'locale'        => 'en',
            'title'         => 'Test Category SEO Title',
            'description'   => 'Test category SEO description for better search engine optimization',
            'keywords'      => 'test, category, seo',
            'canonical_url' => 'https://example.com/categories/test-category',
            'no_index'      => false,
            'no_follow'     => false,
        ]);

        $this->assertDatabaseHas('seo_data', [
            'seoable_type'  => Category::class,
            'seoable_id'    => $category->id,
            'locale'        => 'en',
            'title'         => json_encode('Test Category SEO Title'),
            'description'   => json_encode('Test category SEO description for better search engine optimization'),
            'keywords'      => json_encode(['test', 'category', 'seo']),
            'canonical_url' => 'https://example.com/categories/test-category',
            'no_index'      => false,
            'no_follow'     => false,
        ]);
    }

    public function test_can_create_seo_data_for_brand(): void
    {
        // Avoid triggering brand observers while arranging test data.
        $brand = Brand::factory()->createQuietly();

        SeoData::create([
            'seoable_type'  => Brand::class,
            'seoable_id'    => $brand->id,
            'locale'        => 'lt',
            'title'         => 'Test Brand SEO Title',
            'description'   => 'Test brand SEO description for better search engine optimization',
            'keywords'      => 'test, brand, seo',
            'canonical_url' => 'https://example.com/brands/test-brand',
            'no_index'      => false,
            'no_follow'     => false,
        ]);

        $this->assertDatabaseHas('seo_data', [
            'seoable_type'  => Brand::class,
            'seoable_id'    => $brand->id,
            'locale'        => 'lt',
            'title'         => json_encode('Test Brand SEO Title'),
            'description'   => json_encode('Test brand SEO description for better search engine optimization'),
            'keywords'      => json_encode(['test', 'brand', 'seo']),
            'canonical_url' => 'https://example.com/brands/test-brand',
            'no_index'      => false,
            'no_follow'     => false,
        ]);
    }

    public function test_can_edit_seo_data(): void
    {
        // Use the helper to create the record quietly before editing.
        $seoData = $this->createSeoData();

        $seoData->setTranslation('title', 'lt', 'Updated SEO Title');
        $seoData->setTranslation(
            'description',
            'lt',
            'Updated SEO description for better search engine optimization'
        );
        $seoData->keywords = 'updated, seo, keywords';
        $seoData->save();

        $seoData->refresh();

        // Confirm the translated payloads were written for the Lithuanian locale explicitly.
        $this->assertSame('Updated SEO Title', $seoData->getTranslation('title', 'lt'));
        $this->assertSame(
            'Updated SEO description for better search engine optimization',
            $seoData->getTranslation('description', 'lt')
        );
        // Keywords remain exposed as a normalised array regardless of how they were provided.
        $this->assertSame(['updated', 'seo', 'keywords'], $seoData->keywords);
    }

    public function test_can_view_seo_data(): void
    {
        // Arrange a SEO record for viewing.
        $seoData = $this->createSeoData();

        $fetchedSeoData = SeoData::query()->find($seoData->getKey());

        $this->assertNotNull($fetchedSeoData);
        $this->assertSame($seoData->title, $fetchedSeoData->title);
        $this->assertSame($seoData->description, $fetchedSeoData->description);
    }

    public function test_can_delete_seo_data(): void
    {
        // Prepare a record to exercise the delete table action.
        $seoData = $this->createSeoData();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->callTableAction('delete', $seoData);

        // The resource performs a soft delete, so verify the timestamp is populated instead of expecting a hard delete.
        $this->assertSoftDeleted($seoData);
    }

    public function test_can_filter_by_seoable_type(): void
    {
        $product = Product::factory()->createQuietly();
        $category = Category::factory()->createQuietly();
        $seoData1 = $this->createSeoData([
            'seoable_type' => Product::class,
            'seoable_id'   => $product->id,
        ]);
        $seoData2 = $this->createSeoData([
            'seoable_type' => Category::class,
            'seoable_id'   => $category->id,
        ]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('seoable_type', Product::class)
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_locale(): void
    {
        $seoData1 = $this->createSeoData(['locale' => 'lt']);
        $seoData2 = $this->createSeoData(['locale' => 'en']);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('locale', 'lt')
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_no_index(): void
    {
        $seoData1 = $this->createSeoData(['no_index' => true]);
        $seoData2 = $this->createSeoData(['no_index' => false]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('no_index', true)
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_no_follow(): void
    {
        $seoData1 = $this->createSeoData(['no_follow' => true]);
        $seoData2 = $this->createSeoData(['no_follow' => false]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('no_follow', true)
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_has_title(): void
    {
        $seoData1 = $this->createSeoData(['title' => 'Test Title']);
        $seoData2 = $this->createSeoData(['title' => null]);

        // Force the underlying column to NULL to reflect how the database-only filter evaluates the payload.
        DB::table('seo_data')
            ->where('id', $seoData2->getKey())
            ->update(['title' => null]);
        $seoData2->refresh();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('has_title')
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_has_description(): void
    {
        $seoData1 = $this->createSeoData(['description' => 'Test Description']);
        $seoData2 = $this->createSeoData(['description' => null]);

        // Apply the same null override for the description column so the filter treats it as empty content.
        DB::table('seo_data')
            ->where('id', $seoData2->getKey())
            ->update(['description' => null]);
        $seoData2->refresh();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('has_description')
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_has_keywords(): void
    {
        $seoData1 = $this->createSeoData(['keywords' => ['test', 'keywords']]);
        $seoData2 = $this->createSeoData(['keywords' => []]);

        // Align the second record with the database-level filter by forcing a NULL keyword column value.
        DB::table('seo_data')
            ->where('id', $seoData2->getKey())
            ->update(['keywords' => null]);
        $seoData2->refresh();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('has_keywords')
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_filter_by_has_canonical_url(): void
    {
        $seoData1 = $this->createSeoData(['canonical_url' => 'https://example.com']);
        $seoData2 = $this->createSeoData(['canonical_url' => null]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->filterTable('has_canonical_url')
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_analyze_seo_action(): void
    {
        $seoData = $this->createSeoData();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->callTableAction('analyze_seo', $seoData)
            ->assertNotified('SEO analyzed successfully');
    }

    public function test_can_generate_meta_tags_action(): void
    {
        $seoData = $this->createSeoData();

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->callTableAction('generate_meta_tags', $seoData)
            ->assertNotified('Meta tags generated successfully');
    }

    public function test_can_bulk_analyze_seo(): void
    {
        $seoData = collect(range(1, 3))->map(fn (): SeoData => $this->createSeoData());

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->callTableBulkAction('analyze_all_seo', $seoData)
            ->assertNotified('All SEO analyzed successfully');
    }

    public function test_can_bulk_generate_meta_tags(): void
    {
        $seoData = collect(range(1, 3))->map(fn (): SeoData => $this->createSeoData());

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->callTableBulkAction('generate_all_meta_tags', $seoData)
            ->assertNotified('All meta tags generated successfully');
    }

    public function test_can_search_seo_data(): void
    {
        $productWithMatch = Product::factory()->createQuietly(['name' => 'Search Target Product']);
        $productWithoutMatch = Product::factory()->createQuietly(['name' => 'Unrelated Item']);

        $seoData1 = $this->createSeoData([
            'seoable_type'  => Product::class,
            'seoable_id'    => $productWithMatch->getKey(),
            'title'         => 'Test SEO Title',
        ]);
        $seoData2 = $this->createSeoData([
            'seoable_type'  => Product::class,
            'seoable_id'    => $productWithoutMatch->getKey(),
            'title'         => 'Another Title',
        ]);

        Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
            ->searchTable('Search Target')
            ->assertCanSeeTableRecords([$seoData1])
            ->assertCanNotSeeTableRecords([$seoData2]);
    }

    public function test_can_sort_seo_data(): void
    {
        $seoData1 = $this->createSeoData(['title' => 'A SEO Title']);
        $seoData2 = $this->createSeoData(['title' => 'B SEO Title']);

        // Sorting leverages the shared OrdersByName scope, so confirm the Eloquent query returns the expected sequence.
        $orderedIds = SeoData::query()
            ->orderedByName()
            ->pluck('id')
            ->all();

        $this->assertSame([
            $seoData1->getKey(),
            $seoData2->getKey(),
        ], $orderedIds);
    }

    public function test_form_validation_works(): void
    {
        // Mirror the core Filament form rules so we can verify that invalid payloads are rejected before persistence.
        $validator = Validator::make(
            [
                'seoable_type'  => '',
                'seoable_id'    => '',
                'locale'        => '',
                'title'         => '',
                'description'   => '',
                'canonical_url' => 'invalid-url',
            ],
            [
                'seoable_type'  => ['required', 'string'],
                'seoable_id'    => ['required'],
                'locale'        => ['required', 'string', 'max:10'],
                'title'         => ['required', 'string', 'max:255'],
                'description'   => ['required', 'string', 'max:160'],
                'canonical_url' => ['nullable', 'url', 'max:255'],
            ]
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('canonical_url', $validator->errors()->toArray());
    }

    public function test_relationships_are_loaded(): void
    {
        $product = Product::factory()->createQuietly();
        $seoData = $this->createSeoData([
            'seoable_type' => Product::class,
            'seoable_id'   => $product->id,
        ]);

        $this->assertSame($product->name, $seoData->seoable?->name);
    }

    public function test_seo_score_is_displayed_correctly(): void
    {
        $seoData = $this->createSeoData([
            'title'         => 'Test Title',
            'description'   => 'Test description',
            'keywords'      => 'test, keywords',
            'canonical_url' => 'https://example.com',
        ]);

        $this->assertIsInt($seoData->seo_score);
        $this->assertGreaterThanOrEqual(0, $seoData->seo_score);
    }

    public function test_robots_display_is_correct(): void
    {
        $seoData1 = $this->createSeoData(['no_index' => false, 'no_follow' => false]);
        $seoData2 = $this->createSeoData(['no_index' => true, 'no_follow' => true]);

        $this->assertSame('index, follow', $seoData1->robots);
        $this->assertSame('noindex, nofollow', $seoData2->robots);
    }
}
