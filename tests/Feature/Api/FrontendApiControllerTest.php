<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

final class FrontendApiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cart_count_uses_session_data(): void
    {
        Session::start();

        $this->withSession([
            'cart' => [
                1 => ['quantity' => 2],
                2 => ['quantity' => 3],
            ],
        ]);

        $response = $this->getJson(route('frontend.api.cart.count'));

        $response->assertOk()
            ->assertJson(['count' => 5]);
    }

    public function test_authenticated_cart_count_prioritises_database_items(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Session::start();
        $sessionId = session()->getId();

        $product = Product::factory()->create();

        CartItem::factory()
            ->forUser($user)
            ->forProduct($product)
            ->create(['session_id' => $sessionId, 'quantity' => 2]);

        CartItem::factory()
            ->forUser($user)
            ->create(['quantity' => 1]);

        $response = $this->getJson(route('frontend.api.cart.count'));

        $response->assertOk()
            ->assertJson(['count' => 3]);
    }

    public function test_toggle_wishlist_adds_and_removes_items(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user);

        Session::start();

        $route = route('frontend.api.wishlist.toggle');

        $addResponse = $this->postJson($route, ['product_id' => $product->getKey()]);

        $addResponse->assertOk()
            ->assertJson([
                'added' => true,
                'count' => 1,
            ]);

        $wishlist = UserWishlist::query()->where('user_id', $user->getKey())->first();
        self::assertNotNull($wishlist);

        $this->assertDatabaseHas(WishlistItem::class, [
            'product_id'  => $product->getKey(),
            'wishlist_id' => $wishlist->getKey(),
        ]);

        $removeResponse = $this->postJson($route, ['product_id' => $product->getKey()]);

        $removeResponse->assertOk()
            ->assertJson([
                'added' => false,
                'count' => 0,
            ]);

        $this->assertDatabaseMissing(WishlistItem::class, [
            'product_id' => $product->getKey(),
        ]);
    }

    public function test_toggle_wishlist_requires_authentication(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson(route('frontend.api.wishlist.toggle'), ['product_id' => $product->getKey()]);

        $response->assertUnauthorized();
    }

    public function test_recently_viewed_returns_products_in_recent_order(): void
    {
        $firstProduct = Product::factory()->create();
        $secondProduct = Product::factory()->create();

        Session::start();

        $this->withSession([
            'recently_viewed' => [$secondProduct->getKey(), $firstProduct->getKey()],
        ]);

        $response = $this->getJson(route('frontend.api.recently-viewed'));

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->count(2)
                ->first(fn (AssertableJson $item) => $item
                    ->where('id', $secondProduct->getKey())
                    ->etc()
                )
                ->has('1', fn (AssertableJson $item) => $item
                    ->where('id', $firstProduct->getKey())
                    ->etc()
                )
            );
    }
}
