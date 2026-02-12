<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\PricesRelationManager;
use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductCommentsAndPricesRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        $this->currency = Currency::factory()->create([
            'code'       => 'EUR',
            'is_default' => true,
            'is_active'  => true,
            'is_enabled' => true,
        ]);

        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->product = Product::query()->create([
            'name'           => 'Relation Manager Product',
            'slug'           => 'relation-manager-product',
            'sku'            => 'REL-TEST-001',
            'price'          => 99.99,
            'manage_stock'   => false,
            'stock_quantity' => 0,
            'status'         => 'published',
            'is_enabled'     => true,
            'is_featured'    => false,
            'published_at'   => now(),
        ]);

        $this->actingAs($this->admin);
    }

    public function test_comments_relation_manager_requires_content_without_sql_error(): void
    {
        Livewire::test(CommentsRelationManager::class, [
            'ownerRecord' => $this->product,
            'pageClass'   => EditProduct::class,
        ])
            ->mountTableAction('create')
            ->callMountedTableAction();

        $this->assertDatabaseMissing('comments', [
            'commentable_id'   => $this->product->getKey(),
            'commentable_type' => Product::class,
        ]);
    }

    public function test_comments_relation_manager_can_create_comment(): void
    {
        Livewire::test(CommentsRelationManager::class, [
            'ownerRecord' => $this->product,
            'pageClass'   => EditProduct::class,
        ])
            ->mountTableAction('create')
            ->set('mountedActions.0.data.content', 'Regression test comment')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('comments', [
            'content'          => 'Regression test comment',
            'commentable_id'   => $this->product->getKey(),
            'commentable_type' => Product::class,
            'user_id'          => $this->admin->getKey(),
        ]);
    }

    public function test_prices_relation_manager_sets_polymorphic_columns_when_creating_price(): void
    {
        Livewire::test(PricesRelationManager::class, [
            'ownerRecord' => $this->product,
            'pageClass'   => EditProduct::class,
        ])
            ->mountTableAction('create')
            ->set('mountedActions.0.data.currency_id', $this->currency->getKey())
            ->set('mountedActions.0.data.amount', 123.1234)
            ->set('mountedActions.0.data.is_enabled', true)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $price = Price::query()
            ->where('priceable_id', $this->product->getKey())
            ->where('priceable_type', Product::class)
            ->latest('id')
            ->first();

        $this->assertNotNull($price);
        $this->assertSame('retail', $price->type);
    }
}
