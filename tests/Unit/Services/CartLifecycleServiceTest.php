<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\CartItem;
use App\Services\Cart\CartLifecycleService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class CartLifecycleServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure each test starts with a clean, minimal cart_items table tailored for lifecycle checks.
        Schema::dropIfExists('cart_items');
        Schema::create('cart_items', static function (Blueprint $table): void {
            $table->id();
            $table->string('session_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('minimum_quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->decimal('price', 12, 2)->nullable();
            $table->json('product_snapshot')->nullable();
            $table->json('attributes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('cart_items');

        parent::tearDown();
    }

    public function test_clears_all_carts_for_successful_checkout(): void
    {
        $userId = 1001;
        $primarySession = 'session-primary';
        $secondarySession = 'session-secondary';

        CartItem::query()->create([
            'user_id'          => $userId,
            'session_id'       => $primarySession,
            'quantity'         => 2,
            'minimum_quantity' => 1,
            'unit_price'       => 25.50,
            'total_price'      => 51.00,
            'price'            => 25.50,
            'product_snapshot' => [],
        ]);

        CartItem::query()->create([
            'user_id'          => $userId,
            'session_id'       => $secondarySession,
            'quantity'         => 1,
            'minimum_quantity' => 1,
            'unit_price'       => 15.00,
            'total_price'      => 15.00,
            'price'            => 15.00,
            'product_snapshot' => [],
        ]);

        app(CartLifecycleService::class)->clearAfterCheckout($userId, $primarySession, 'paid');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_does_not_clear_cart_for_partial_payment(): void
    {
        $userId = 2002;
        $sessionId = 'session-partial';

        $cartItem = CartItem::query()->create([
            'user_id'          => $userId,
            'session_id'       => $sessionId,
            'quantity'         => 3,
            'minimum_quantity' => 1,
            'unit_price'       => 12.00,
            'total_price'      => 36.00,
            'price'            => 12.00,
            'product_snapshot' => [],
        ]);

        app(CartLifecycleService::class)->clearAfterCheckout($userId, $sessionId, 'partial');

        $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id]);
    }

    public function test_clears_cart_when_session_expired_but_user_present(): void
    {
        $userId = 3003;

        CartItem::query()->create([
            'user_id'          => $userId,
            'session_id'       => 'expired-session',
            'quantity'         => 1,
            'minimum_quantity' => 1,
            'unit_price'       => 9.99,
            'total_price'      => 9.99,
            'price'            => 9.99,
            'product_snapshot' => [],
        ]);

        app(CartLifecycleService::class)->clearAfterCheckout($userId, null, 'paid');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_clears_guest_cart_on_abandoned_checkout(): void
    {
        $sessionId = 'guest-session';
        $cartItem = CartItem::query()->create([
            'session_id'       => $sessionId,
            'quantity'         => 2,
            'minimum_quantity' => 1,
            'unit_price'       => 18.75,
            'total_price'      => 37.50,
            'price'            => 18.75,
            'product_snapshot' => [],
        ]);

        app(CartLifecycleService::class)->clearForAbandonedCheckout(null, $sessionId);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }
}
