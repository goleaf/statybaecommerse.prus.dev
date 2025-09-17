<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SubscriberResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_subscriber(): void
    {
        $subscriberData = [
            'email' => 'test@example.com',
            'type' => 'newsletter',
            'is_active' => true,
            'is_verified' => false,
        ];

        $subscriber = Subscriber::create($subscriberData);

        $this->assertDatabaseHas('subscribers', [
            'email' => 'test@example.com',
            'type' => 'newsletter',
            'is_active' => true,
            'is_verified' => false,
        ]);

        $this->assertEquals('test@example.com', $subscriber->email);
        $this->assertEquals('newsletter', $subscriber->type);
        $this->assertTrue($subscriber->is_active);
        $this->assertFalse($subscriber->is_verified);
    }

    public function test_can_update_subscriber(): void
    {
        $subscriber = Subscriber::factory()->create();

        $subscriber->update([
            'type' => 'promotions',
            'is_verified' => true,
        ]);

        $this->assertEquals('promotions', $subscriber->type);
        $this->assertTrue($subscriber->is_verified);
    }

    public function test_can_filter_subscribers_by_type(): void
    {
        Subscriber::factory()->create(['type' => 'newsletter']);
        Subscriber::factory()->create(['type' => 'promotions']);

        $newsletterSubscribers = Subscriber::where('type', 'newsletter')->get();
        $promotionSubscribers = Subscriber::where('type', 'promotions')->get();

        $this->assertCount(1, $newsletterSubscribers);
        $this->assertCount(1, $promotionSubscribers);
        $this->assertEquals('newsletter', $newsletterSubscribers->first()->type);
        $this->assertEquals('promotions', $promotionSubscribers->first()->type);
    }

    public function test_can_filter_subscribers_by_verification_status(): void
    {
        Subscriber::factory()->create(['is_verified' => true]);
        Subscriber::factory()->create(['is_verified' => false]);

        $verifiedSubscribers = Subscriber::where('is_verified', true)->get();
        $unverifiedSubscribers = Subscriber::where('is_verified', false)->get();

        $this->assertCount(1, $verifiedSubscribers);
        $this->assertCount(1, $unverifiedSubscribers);
        $this->assertTrue($verifiedSubscribers->first()->is_verified);
        $this->assertFalse($unverifiedSubscribers->first()->is_verified);
    }

    public function test_can_filter_subscribers_by_active_status(): void
    {
        Subscriber::factory()->create(['is_active' => true]);
        Subscriber::factory()->create(['is_active' => false]);

        $activeSubscribers = Subscriber::where('is_active', true)->get();
        $inactiveSubscribers = Subscriber::where('is_active', false)->get();

        $this->assertCount(1, $activeSubscribers);
        $this->assertCount(1, $inactiveSubscribers);
        $this->assertTrue($activeSubscribers->first()->is_active);
        $this->assertFalse($inactiveSubscribers->first()->is_active);
    }

    public function test_can_filter_subscribers_by_date(): void
    {
        Subscriber::factory()->create([
            'email' => 'today@example.com',
            'created_at' => now(),
        ]);
        
        Subscriber::factory()->create([
            'email' => 'yesterday@example.com',
            'created_at' => now()->subDay(),
        ]);

        $todaySubscribers = Subscriber::whereDate('created_at', today())->get();
        $yesterdaySubscribers = Subscriber::whereDate('created_at', now()->subDay())->get();

        $this->assertCount(1, $todaySubscribers);
        $this->assertCount(1, $yesterdaySubscribers);
        $this->assertEquals('today@example.com', $todaySubscribers->first()->email);
        $this->assertEquals('yesterday@example.com', $yesterdaySubscribers->first()->email);
    }

    public function test_can_soft_delete_subscriber(): void
    {
        $subscriber = Subscriber::factory()->create();

        $subscriber->delete();

        $this->assertSoftDeleted('subscribers', [
            'id' => $subscriber->id,
        ]);
    }
}
