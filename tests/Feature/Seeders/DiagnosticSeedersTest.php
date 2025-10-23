<?php

declare(strict_types=1);

namespace Tests\Feature\Seeders;

use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\Partner;
use App\Models\PartnerTier;
use Database\Seeders\NotificationSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\PartnerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DiagnosticSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_seeder_creates_notifications(): void
    {
        $this->seed(NotificationSeeder::class);

        $this->assertGreaterThan(0, Notification::count());
        $this->assertTrue(Notification::query()->whereNotNull('notifiable_id')->exists());
    }

    public function test_order_seeder_creates_orders_with_items_and_shipping(): void
    {
        $this->seed(OrderSeeder::class);

        $this->assertSame(24, Order::count());
        $this->assertTrue(OrderItem::query()->exists());
        $this->assertSame(Order::count(), OrderShipping::count());
        $this->assertTrue(Order::query()->where('payment_status', 'paid')->exists());
    }

    public function test_partner_seeder_is_idempotent_and_creates_expected_entities(): void
    {
        $this->seed(PartnerSeeder::class);
        $this->seed(PartnerSeeder::class);

        $this->assertGreaterThanOrEqual(3, PartnerTier::count());
        $this->assertGreaterThanOrEqual(5, Partner::count());
        $this->assertSame(
            Partner::count(),
            Partner::query()->distinct()->count('code')
        );
    }
}
