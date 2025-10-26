<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\RecommendationBlockResource\Pages\CreateRecommendationBlock;
use App\Models\Product;
use App\Models\RecommendationBlock;
use App\Models\User;
use App\Services\Cart\CartService;
use App\Services\Shared\CacheService;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithFilamentPivotTables;
use Tests\TestCase;

final class RecommendationBlockResourcePivotSyncTest extends TestCase
{
    use InteractsWithFilamentPivotTables;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureFilamentPivotTablesMigrated();
        $this->resetFilamentPivotTables();
        $this->resolveAdminPanel();

        config([
            'app.locale'          => 'en',
            'app.fallback_locale' => 'en',
            'activitylog.enabled' => false,
        ]);

        app()->setLocale('en');

        app()->singleton(CartService::class, fn () => new class
        {
            public function getCount(?int $userId, ?string $sessionId): int
            {
                return 0;
            }

            public function getSessionItems(): array
            {
                return [];
            }

            public function getSessionCount(): int
            {
                return 0;
            }
        });

        app()->singleton(CacheService::class, fn () => new class
        {
            public function rememberShort(string $key, callable $callback, ?int $ttl = null): mixed
            {
                return collect();
            }

            public function rememberDefault(string $key, callable $callback, ?int $ttl = null): mixed
            {
                return collect();
            }

            public function rememberLong(string $key, callable $callback, ?int $ttl = null): mixed
            {
                return collect();
            }

            public function forgetPattern(string $pattern): void {}
        });

        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_it_synchronizes_selected_products_on_create(): void
    {
        $products = Product::factory()
            ->published()
            ->count(3)
            ->state(['brand_id' => null])
            ->create();

        Livewire::test(CreateRecommendationBlock::class)
            ->fillForm([
                'name'             => 'homepage-featured',
                'title'            => 'Homepage Featured',
                'description'      => 'Block used on homepage for featured products.',
                'type'             => 'featured',
                'position'         => 'top',
                'product_ids'      => $products->pluck('id')->all(),
                'max_products'     => 3,
                'is_active'        => true,
                'show_title'       => true,
                'show_description' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $block = RecommendationBlock::query()->first();

        $this->assertNotNull($block);

        $this->assertEqualsCanonicalizing(
            $products->pluck('id')->all(),
            $block->products()->pluck('products.id')->all()
        );

        foreach ($products as $product) {
            $this->assertDatabaseHas('recommendation_block_products', [
                'recommendation_block_id' => $block->getKey(),
                'product_id'              => $product->getKey(),
            ]);
        }
    }
}
