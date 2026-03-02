<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Components;

use App\Livewire\Components\ProductRequestForm;
use App\Models\Product;
use App\Models\ProductRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductRequestFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_product_request_without_user_id(): void
    {
        $product = Product::query()->create([
            'name'           => 'Guest Request Product',
            'slug'           => 'guest-request-product',
            'sku'            => 'GUEST-REQ-001',
            'price'          => 99.99,
            'manage_stock'   => false,
            'stock_quantity' => 0,
            'status'         => 'published',
            'is_enabled'     => true,
            'is_featured'    => false,
            'published_at'   => now(),
        ]);

        Livewire::test(ProductRequestForm::class, [
            'product' => $product,
        ])
            ->set('name', 'Guest Visitor')
            ->set('email', 'guest.visitor@example.com')
            ->set('phone', '+37065555555')
            ->set('message', 'Need a tailored offer.')
            ->set('requested_quantity', 3)
            ->call('submitRequest');

        $this->assertDatabaseHas('product_requests', [
            'product_id'         => $product->getKey(),
            'user_id'            => null,
            'name'               => 'Guest Visitor',
            'email'              => 'guest.visitor@example.com',
            'requested_quantity' => 3,
            'status'             => ProductRequest::STATUS_PENDING,
        ]);
    }
}

