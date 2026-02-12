<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\RelationManagers\RequestsRelationManager;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductRequestsRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        Currency::factory()->create([
            'code'       => 'EUR',
            'is_default' => true,
            'is_active'  => true,
            'is_enabled' => true,
        ]);

        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->product = Product::query()->create([
            'name'           => 'Requests Relation Product',
            'slug'           => 'requests-relation-product',
            'sku'            => 'REL-REQ-001',
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

    public function test_product_edit_requests_relation_page_does_not_return_server_error(): void
    {
        ProductRequest::query()->create([
            'product_id'         => $this->product->getKey(),
            'user_id'            => $this->admin->getKey(),
            'name'               => 'Need more quantity',
            'email'              => 'request@example.com',
            'phone'              => '+37060000000',
            'message'            => 'Please confirm availability.',
            'requested_quantity' => 3,
            'status'             => ProductRequest::STATUS_PENDING,
        ]);

        $response = $this->get("/admin/products/{$this->product->getKey()}/edit?relation=5");

        $this->assertLessThan(500, $response->status());
    }

    public function test_requests_relation_manager_lists_existing_requests_and_hides_create_action(): void
    {
        $request = ProductRequest::query()->create([
            'product_id'         => $this->product->getKey(),
            'user_id'            => $this->admin->getKey(),
            'name'               => 'Request from relation test',
            'email'              => 'relation-request@example.com',
            'phone'              => '+37061111111',
            'message'            => 'Need delivery date.',
            'requested_quantity' => 5,
            'status'             => ProductRequest::STATUS_IN_PROGRESS,
        ]);

        Livewire::test(RequestsRelationManager::class, [
            'ownerRecord' => $this->product,
            'pageClass'   => EditProduct::class,
        ])
            ->assertCanSeeTableRecords([$request])
            ->assertTableActionDoesNotExist('create');
    }
}
