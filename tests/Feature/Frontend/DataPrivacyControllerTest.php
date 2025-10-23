<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class DataPrivacyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_user_can_export_personal_data(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'preferences' => ['language' => 'en'],
            'privacy_settings' => ['analytics' => false],
        ]);

        Address::factory()->for($user)->create([
            'type' => 'shipping',
            'is_default' => true,
        ]);

        $product = Product::factory()->create();

        $order = Order::factory()->for($user)->create([
            'currency' => 'EUR',
            'total' => 42.50,
        ]);

        $productKey = $product->getKey();
        $productKeyString = is_scalar($productKey) ? (string) $productKey : '';

        OrderItem::factory()->for($order)->create([
            'product_id' => $product->getKey(),
            'name' => $product->name,
            'sku' => 'SKU-'.$productKeyString,
            'quantity' => 1,
            'price' => 42.50,
            'total' => 42.50,
        ]);

        Review::factory()->for($user)->for($product)->create();

        $wishlist = UserWishlist::factory()
            ->for($user)
            ->default()
            ->create();

        WishlistItem::factory()
            ->for($wishlist, 'wishlist')
            ->for($product)
            ->create([
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $response = $this->actingAs($user)
            ->post(route('frontend.profile.data.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');
        $response->assertHeader('content-disposition');

        /** @var array<string, mixed> $payload */
        $payload = json_decode($response->streamedContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('meta', $payload);
        $this->assertArrayHasKey('profile', $payload);
        $this->assertArrayHasKey('addresses', $payload);
        $this->assertArrayHasKey('orders', $payload);
        $this->assertArrayHasKey('wishlist', $payload);
        $this->assertArrayHasKey('reviews', $payload);

        /** @var array<string, mixed> $meta */
        $meta = $payload['meta'];

        /** @var array<string, mixed> $profile */
        $profile = $payload['profile'];

        $this->assertSame($user->getKey(), $meta['user_id']);
        $this->assertSame($user->email, $profile['email']);
        $this->assertNotEmpty($payload['addresses']);
        $this->assertNotEmpty($payload['orders']);
        $this->assertNotEmpty($payload['wishlist']);
        $this->assertNotEmpty($payload['reviews']);
    }

    public function test_user_can_delete_account(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret-pass'),
        ]);

        Address::factory()->for($user)->create();

        $response = $this->actingAs($user)
            ->from(route('frontend.profile.index'))
            ->delete(route('frontend.profile.data.destroy'), [
                'password' => 'secret-pass',
                'confirm_deletion' => '1',
            ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success', __('translations.profile_delete_success'));

        $this->assertGuest();

        $user->refresh();
        $this->assertNotNull($user->deleted_at);
        $userKey = $user->getKey();
        $userKeyString = is_scalar($userKey) ? (string) $userKey : '';

        $this->assertStringStartsWith('deleted-user-'.$userKeyString, $user->email);
        $this->assertSame('Deleted User', $user->name);
        $this->assertSame(0, Address::query()->where('user_id', $user->getKey())->count());
    }
}
