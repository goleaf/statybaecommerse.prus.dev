<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ProductRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_displays_form_for_requestable_product(): void
    {
        // Arrange: authenticate a user and prepare a requestable product instance.
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'is_requestable' => true,
            'requests_count' => 0,
        ]);

        // Act: open the request creation form.
        $response = $this->actingAs($user)->get(route('product-requests.create', $product));

        // Assert: confirm the correct view is rendered with the bound product.
        $response->assertOk();
        $response->assertViewIs('products.request-form');
        $response->assertViewHas('product', static fn (Product $viewProduct): bool => $viewProduct->is($product));
    }

    public function test_create_returns_not_found_for_non_requestable_product(): void
    {
        // Arrange: a non-requestable product should not surface the form.
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'is_requestable' => false,
        ]);

        // Act & Assert: expect a 404 status when the product is not requestable.
        $this->actingAs($user)
            ->get(route('product-requests.create', $product))
            ->assertNotFound();
    }

    public function test_store_creates_request_and_increments_counter(): void
    {
        // Arrange: build a requestable product and payload for the submission.
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'is_requestable' => true,
            'requests_count' => 0,
        ]);
        $payload = [
            'product_id'         => $product->getKey(),
            'name'               => 'Test Requester',
            'email'              => 'requester@example.com',
            'phone'              => '+37060000000',
            'message'            => 'Please let me know when this is back in stock.',
            'requested_quantity' => 2,
        ];

        // Act: submit the store request.
        $response = $this->actingAs($user)->post(route('product-requests.store'), $payload);

        // Assert: ensure we redirect back to the product page with a persisted record and counter update.
        $expectedRedirect = Route::has('products.show')
            ? route('products.show', $product)
            : route('frontend.products.show', $product);

        $response->assertRedirect($expectedRedirect);
        $this->assertDatabaseHas(ProductRequest::class, [
            'product_id'         => $product->getKey(),
            'user_id'            => $user->getKey(),
            'requested_quantity' => 2,
            'status'             => ProductRequest::STATUS_PENDING,
        ]);
        $reloadedProduct = $product->fresh();
        self::assertInstanceOf(Product::class, $reloadedProduct);
        $this->assertSame(1, $reloadedProduct->getRequestsCount());
    }

    public function test_store_returns_error_when_product_not_requestable(): void
    {
        // Arrange: create a product that cannot accept requests and craft the payload.
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'is_requestable' => false,
            'requests_count' => 0,
        ]);
        $payload = [
            'product_id'         => $product->getKey(),
            'name'               => 'Disallowed Requester',
            'email'              => 'blocked@example.com',
            'phone'              => null,
            'message'            => 'Attempting to request a non-requestable product.',
            'requested_quantity' => 1,
        ];

        // Act: submit the payload against a non-requestable product.
        $response = $this->actingAs($user)->from(route('product-requests.create', $product))->post(route('product-requests.store'), $payload);

        // Assert: confirm validation style feedback and that no data was stored or counters updated.
        $response->assertRedirect(route('product-requests.create', $product));
        $response->assertSessionHasErrors('error');
        $response->assertSessionHasInput('name', 'Disallowed Requester');
        $this->assertDatabaseCount('product_requests', 0);
        $reloadedProduct = $product->fresh();
        self::assertInstanceOf(Product::class, $reloadedProduct);
        $this->assertSame(0, $reloadedProduct->getRequestsCount());
    }

    public function test_cancel_marks_request_as_cancelled(): void
    {
        // Arrange: persist a pending request owned by the acting user.
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'is_requestable' => true,
            'requests_count' => 0,
        ]);
        $productRequest = ProductRequest::factory()->create([
            'product_id' => $product->getKey(),
            'user_id'    => $user->getKey(),
            'status'     => ProductRequest::STATUS_PENDING,
        ]);

        // Act: cancel the pending request.
        $response = $this->actingAs($user)->patch(route('product-requests.cancel', $productRequest));

        // Assert: ensure the status transition is reflected in the database and the user is redirected.
        $response->assertRedirect();
        $cancelledRequest = ProductRequest::withoutGlobalScopes()->findOrFail($productRequest->getKey());
        self::assertInstanceOf(ProductRequest::class, $cancelledRequest);
        $this->assertSame(ProductRequest::STATUS_CANCELLED, $cancelledRequest->status);
    }

    public function test_cancel_returns_error_for_terminal_request(): void
    {
        // Arrange: create a completed request that should not allow cancellation.
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'is_requestable' => true,
            'requests_count' => 0,
        ]);
        $productRequest = ProductRequest::factory()->create([
            'product_id' => $product->getKey(),
            'user_id'    => $user->getKey(),
            'status'     => ProductRequest::STATUS_COMPLETED,
        ]);

        // Act: attempt to cancel a completed request.
        $response = $this->actingAs($user)->patch(route('product-requests.cancel', $productRequest));

        // Assert: the controller should reject the action with an error message.
        $response->assertRedirect();
        $response->assertSessionHasErrors('error');
        $completedRequest = ProductRequest::withoutGlobalScopes()->findOrFail($productRequest->getKey());
        self::assertInstanceOf(ProductRequest::class, $completedRequest);
        $this->assertSame(ProductRequest::STATUS_COMPLETED, $completedRequest->status);
    }
}
