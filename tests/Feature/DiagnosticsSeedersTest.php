<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Partner;
use App\Models\PartnerTier;
use Database\Seeders\NotificationSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\PartnerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Feature tests that replace the legacy artisan diagnostics commands with focused coverage checks.
 */
final class DiagnosticsSeedersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure the notification seeder executes successfully and persists notification records.
     */
    public function test_notification_seeder_creates_records(): void
    {
        Artisan::call('db:seed', ['--class' => NotificationSeeder::class, '--no-interaction' => true]);

        $this->assertTrue(Notification::query()->exists(), 'The notification seeder should create at least one notification.');
    }

    /**
     * Confirm the comprehensive order seeder builds orders with associated order items.
     */
    public function test_order_seeder_builds_orders_with_items(): void
    {
        Artisan::call('db:seed', ['--class' => OrderSeeder::class, '--no-interaction' => true]);

        $this->assertGreaterThan(0, Order::query()->count(), 'The order seeder should create orders.');
        $this->assertTrue(OrderItem::query()->exists(), 'Seeded orders should include order items.');
    }

    /**
     * Verify the partner seeder provisions tiers and partner records without throwing exceptions.
     */
    public function test_partner_seeder_populates_tiers_and_partners(): void
    {
        Artisan::call('db:seed', ['--class' => PartnerSeeder::class, '--no-interaction' => true]);

        $this->assertGreaterThanOrEqual(3, PartnerTier::query()->count(), 'Expected partner tiers to be seeded.');
        $this->assertGreaterThan(0, Partner::query()->count(), 'Expected partner records to be seeded.');
    }
}
