<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\RecommendationCacheResource\Pages\CreateRecommendationCache;
use App\Filament\Resources\RecommendationCacheResource\Pages\EditRecommendationCache;
use App\Filament\Resources\RecommendationCacheResource\Pages\ListRecommendationCaches;
use App\Filament\Resources\RecommendationCacheResource\Pages\ViewRecommendationCache;
use App\Models\Product;
use App\Models\RecommendationBlock;
use App\Models\RecommendationCache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecommendationCacheResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_recommendation_caches(): void
    {
        RecommendationCache::factory()->count(3)->create();

        Livewire::test(ListRecommendationCaches::class)
            ->assertCanSeeTableRecords(RecommendationCache::all());
    }

    public function test_can_create_recommendation_cache(): void
    {
        $block = RecommendationBlock::factory()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Livewire::test(CreateRecommendationCache::class)
            ->fillForm([
                'cache_key' => 'test-cache-key',
                'block_id' => $block->id,
                'user_id' => $user->id,
                'product_id' => $product->id,
                'context_type' => 'homepage',
                'hit_count' => 0,
                'expires_at' => now()->addHours(24),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_cache', [
            'cache_key' => 'test-cache-key',
            'block_id' => $block->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_can_edit_recommendation_cache(): void
    {
        $cache = RecommendationCache::factory()->create();
        $newBlock = RecommendationBlock::factory()->create();

        Livewire::test(EditRecommendationCache::class, [
            'record' => $cache->getRouteKey(),
        ])
            ->fillForm([
                'block_id' => $newBlock->id,
                'hit_count' => 5,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_cache', [
            'id' => $cache->id,
            'block_id' => $newBlock->id,
            'hit_count' => 5,
        ]);
    }

    public function test_can_delete_recommendation_cache(): void
    {
        $cache = RecommendationCache::factory()->create();

        Livewire::test(EditRecommendationCache::class, [
            'record' => $cache->getRouteKey(),
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('recommendation_cache', [
            'id' => $cache->id,
        ]);
    }

    public function test_can_view_recommendation_cache(): void
    {
        $cache = RecommendationCache::factory()->create();

        Livewire::test(ViewRecommendationCache::class, [
            'record' => $cache->getRouteKey(),
        ])
            ->assertSee($cache->cache_key);
    }

    public function test_can_filter_recommendation_caches_by_block(): void
    {
        $block1 = RecommendationBlock::factory()->create(['name' => 'Block 1']);
        $block2 = RecommendationBlock::factory()->create(['name' => 'Block 2']);

        RecommendationCache::factory()->create(['block_id' => $block1->id]);
        RecommendationCache::factory()->create(['block_id' => $block2->id]);

        Livewire::test(ListRecommendationCaches::class)
            ->filterTable('block_id', $block1->id)
            ->assertCanSeeTableRecords(RecommendationCache::where('block_id', $block1->id)->get())
            ->assertCanNotSeeTableRecords(RecommendationCache::where('block_id', $block2->id)->get());
    }

    public function test_can_filter_recommendation_caches_by_user(): void
    {
        $user1 = User::factory()->create(['name' => 'User 1']);
        $user2 = User::factory()->create(['name' => 'User 2']);

        RecommendationCache::factory()->create(['user_id' => $user1->id]);
        RecommendationCache::factory()->create(['user_id' => $user2->id]);

        Livewire::test(ListRecommendationCaches::class)
            ->filterTable('user_id', $user1->id)
            ->assertCanSeeTableRecords(RecommendationCache::where('user_id', $user1->id)->get())
            ->assertCanNotSeeTableRecords(RecommendationCache::where('user_id', $user2->id)->get());
    }

    public function test_can_filter_recommendation_caches_by_product(): void
    {
        $product1 = Product::factory()->create(['name' => 'Product 1']);
        $product2 = Product::factory()->create(['name' => 'Product 2']);

        RecommendationCache::factory()->create(['product_id' => $product1->id]);
        RecommendationCache::factory()->create(['product_id' => $product2->id]);

        Livewire::test(ListRecommendationCaches::class)
            ->filterTable('product_id', $product1->id)
            ->assertCanSeeTableRecords(RecommendationCache::where('product_id', $product1->id)->get())
            ->assertCanNotSeeTableRecords(RecommendationCache::where('product_id', $product2->id)->get());
    }

    public function test_can_filter_recommendation_caches_by_context_type(): void
    {
        RecommendationCache::factory()->create(['context_type' => 'homepage']);
        RecommendationCache::factory()->create(['context_type' => 'product']);

        Livewire::test(ListRecommendationCaches::class)
            ->filterTable('context_type', 'homepage')
            ->assertCanSeeTableRecords(RecommendationCache::where('context_type', 'homepage')->get())
            ->assertCanNotSeeTableRecords(RecommendationCache::where('context_type', 'product')->get());
    }

    public function test_can_search_recommendation_caches_by_cache_key(): void
    {
        RecommendationCache::factory()->create(['cache_key' => 'homepage-recommendations']);
        RecommendationCache::factory()->create(['cache_key' => 'product-recommendations']);

        Livewire::test(ListRecommendationCaches::class)
            ->searchTable('homepage')
            ->assertCanSeeTableRecords(RecommendationCache::where('cache_key', 'like', '%homepage%')->get())
            ->assertCanNotSeeTableRecords(RecommendationCache::where('cache_key', 'like', '%product%')->get());
    }

    public function test_can_bulk_delete_recommendation_caches(): void
    {
        $caches = RecommendationCache::factory()->count(3)->create();

        Livewire::test(ListRecommendationCaches::class)
            ->callTableBulkAction('delete', $caches)
            ->assertHasNoTableBulkActionErrors();

        foreach ($caches as $cache) {
            $this->assertDatabaseMissing('recommendation_cache', [
                'id' => $cache->id,
            ]);
        }
    }

    public function test_validation_requires_cache_key(): void
    {
        $block = RecommendationBlock::factory()->create();
        $user = User::factory()->create();

        Livewire::test(CreateRecommendationCache::class)
            ->fillForm([
                'cache_key' => '',
                'block_id' => $block->id,
                'user_id' => $user->id,
                'expires_at' => now()->addHours(24),
            ])
            ->call('create')
            ->assertHasFormErrors(['cache_key' => 'required']);
    }

    public function test_validation_requires_unique_cache_key(): void
    {
        RecommendationCache::factory()->create(['cache_key' => 'existing-key']);
        $block = RecommendationBlock::factory()->create();
        $user = User::factory()->create();

        Livewire::test(CreateRecommendationCache::class)
            ->fillForm([
                'cache_key' => 'existing-key',
                'block_id' => $block->id,
                'user_id' => $user->id,
                'expires_at' => now()->addHours(24),
            ])
            ->call('create')
            ->assertHasFormErrors(['cache_key' => 'unique']);
    }

    public function test_validation_requires_block_id(): void
    {
        $user = User::factory()->create();

        Livewire::test(CreateRecommendationCache::class)
            ->fillForm([
                'cache_key' => 'test-key',
                'block_id' => null,
                'user_id' => $user->id,
                'expires_at' => now()->addHours(24),
            ])
            ->call('create')
            ->assertHasFormErrors(['block_id' => 'required']);
    }

    public function test_validation_requires_user_id(): void
    {
        $block = RecommendationBlock::factory()->create();

        Livewire::test(CreateRecommendationCache::class)
            ->fillForm([
                'cache_key' => 'test-key',
                'block_id' => $block->id,
                'user_id' => null,
                'expires_at' => now()->addHours(24),
            ])
            ->call('create')
            ->assertHasFormErrors(['user_id' => 'required']);
    }

    public function test_validation_requires_expires_at(): void
    {
        $block = RecommendationBlock::factory()->create();
        $user = User::factory()->create();

        Livewire::test(CreateRecommendationCache::class)
            ->fillForm([
                'cache_key' => 'test-key',
                'block_id' => $block->id,
                'user_id' => $user->id,
                'expires_at' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['expires_at' => 'required']);
    }

    public function test_can_see_block_relationship(): void
    {
        $block = RecommendationBlock::factory()->create();
        $cache = RecommendationCache::factory()->create(['block_id' => $block->id]);

        Livewire::test(EditRecommendationCache::class, [
            'record' => $cache->getRouteKey(),
        ])
            ->assertFormSet([
                'block_id' => $block->id,
            ]);
    }

    public function test_can_see_user_relationship(): void
    {
        $user = User::factory()->create();
        $cache = RecommendationCache::factory()->create(['user_id' => $user->id]);

        Livewire::test(EditRecommendationCache::class, [
            'record' => $cache->getRouteKey(),
        ])
            ->assertFormSet([
                'user_id' => $user->id,
            ]);
    }

    public function test_can_see_product_relationship(): void
    {
        $product = Product::factory()->create();
        $cache = RecommendationCache::factory()->create(['product_id' => $product->id]);

        Livewire::test(EditRecommendationCache::class, [
            'record' => $cache->getRouteKey(),
        ])
            ->assertFormSet([
                'product_id' => $product->id,
            ]);
    }

    public function test_can_handle_expired_caches(): void
    {
        $expiredCache = RecommendationCache::factory()->create([
            'expires_at' => now()->subHours(1),
        ]);
        $validCache = RecommendationCache::factory()->create([
            'expires_at' => now()->addHours(1),
        ]);

        $this->assertTrue($expiredCache->isExpired());
        $this->assertFalse($validCache->isExpired());
    }

    public function test_can_increment_hit_count(): void
    {
        $cache = RecommendationCache::factory()->create(['hit_count' => 0]);

        $cache->incrementHitCount();

        $this->assertEquals(1, $cache->fresh()->hit_count);
    }

    public function test_can_generate_cache_key(): void
    {
        $cacheKey = RecommendationCache::generateCacheKey(
            'homepage',
            1,
            2,
            'product',
            ['category' => 'electronics']
        );

        $this->assertStringContainsString('homepage', $cacheKey);
        $this->assertStringContainsString('user:1', $cacheKey);
        $this->assertStringContainsString('product:2', $cacheKey);
        $this->assertStringContainsString('context:product', $cacheKey);
    }
}
