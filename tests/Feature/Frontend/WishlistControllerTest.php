<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WishlistControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_wishlist_items(): void
    {
        $user = User::factory()->create();
        $wishlist = UserWishlist::factory()->for($user)->default()->create();
        $item = WishlistItem::factory()->for($wishlist, 'wishlist')->create([
            'product_id' => Product::factory()->create()->id,
            'variant_id' => null,
        ]);

        $response = $this->actingAs($user)->get(route('frontend.wishlist.index'));

        $response->assertOk();
        $response->assertSee(e($item->display_name));
    }

    public function test_add_creates_item_and_returns_json(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('frontend.wishlist.add'), [
                'product_id' => $product->id,
                'quantity' => 2,
                'notes' => 'Add for later',
            ]);

        $response
            ->assertOk()
            ->assertJsonFragment([
                'status' => 'added',
                'message' => __('Product added to your wishlist.'),
            ]);

        $wishlist = UserWishlist::query()
            ->where('user_id', $user->id)
            ->where('is_default', true)
            ->first();

        $this->assertNotNull($wishlist);

        $this->assertDatabaseHas('wishlist_items', [
            'wishlist_id' => $wishlist->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'notes' => 'Add for later',
        ]);
    }

    public function test_remove_deletes_item(): void
    {
        $user = User::factory()->create();
        $wishlist = UserWishlist::factory()->for($user)->default()->create();
        $product = Product::factory()->create();
        $item = WishlistItem::factory()->for($wishlist, 'wishlist')->create([
            'product_id' => $product->id,
            'variant_id' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('frontend.wishlist.remove'), [
                'product_id' => $product->id,
            ]);

        $response
            ->assertOk()
            ->assertJsonFragment([
                'status' => 'removed',
                'message' => __('Product removed from your wishlist.'),
            ]);

        $this->assertDatabaseMissing('wishlist_items', [
            'id' => $item->id,
        ]);
    }

    public function test_clear_removes_all_items(): void
    {
        $user = User::factory()->create();
        $wishlist = UserWishlist::factory()->for($user)->default()->create();

        foreach (range(1, 3) as $i) {
            WishlistItem::factory()->for($wishlist, 'wishlist')->create([
                'product_id' => Product::factory()->create()->id,
                'variant_id' => null,
            ]);
        }

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('frontend.wishlist.clear'));

        $response
            ->assertOk()
            ->assertJsonFragment([
                'status' => 'cleared',
                'message' => __('Your wishlist has been cleared.'),
                'wishlist_count' => 0,
            ]);

        $this->assertDatabaseCount('wishlist_items', 0);
    }

    public function test_add_rejects_variant_not_belonging_to_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $otherProductVariant = ProductVariant::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('frontend.wishlist.add'), [
                'product_id' => $product->id,
                'variant_id' => $otherProductVariant->id,
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                'status' => 'error',
            ]);

        $this->assertDatabaseCount('wishlist_items', 0);
    }
}
