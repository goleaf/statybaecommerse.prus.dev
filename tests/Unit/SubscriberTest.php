<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SubscriberTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscriber_fillable_attributes(): void
    {
        $subscriber = new Subscriber;
        $fillable = $subscriber->getFillable();

        $expectedFillable = [
            'user_id', 'email', 'first_name', 'last_name', 'phone', 'company',
            'job_title', 'interests', 'source', 'status', 'subscribed_at',
            'unsubscribed_at', 'last_email_sent_at', 'email_count', 'metadata',
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_subscriber_casts(): void
    {
        $subscriber = new Subscriber;
        $casts = $subscriber->getCasts();

        $this->assertArrayHasKey('interests', $casts);
        $this->assertArrayHasKey('metadata', $casts);
        $this->assertArrayHasKey('subscribed_at', $casts);
        $this->assertArrayHasKey('unsubscribed_at', $casts);
        $this->assertArrayHasKey('last_email_sent_at', $casts);
        $this->assertArrayHasKey('email_count', $casts);

        $this->assertEquals('array', $casts['interests']);
        $this->assertEquals('array', $casts['metadata']);
        $this->assertEquals('datetime', $casts['subscribed_at']);
        $this->assertEquals('datetime', $casts['unsubscribed_at']);
        $this->assertEquals('datetime', $casts['last_email_sent_at']);
        $this->assertEquals('integer', $casts['email_count']);
    }

    public function test_subscriber_uses_soft_deletes(): void
    {
        $subscriber = new Subscriber;
        $this->assertTrue(in_array('SoftDeletes', class_uses($subscriber)));
    }

    public function test_subscriber_uses_has_factory(): void
    {
        $subscriber = new Subscriber;
        $this->assertTrue(in_array('HasFactory', class_uses($subscriber)));
    }

    public function test_subscriber_relationship_with_user(): void
    {
        $user = User::factory()->create();
        $subscriber = Subscriber::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $subscriber->user);
        $this->assertEquals($user->id, $subscriber->user->id);
    }

    public function test_subscriber_full_name_accessor(): void
    {
        $subscriber = Subscriber::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $subscriber->full_name);
    }

    public function test_subscriber_full_name_with_empty_names(): void
    {
        $subscriber = Subscriber::factory()->create([
            'first_name' => '',
            'last_name' => '',
        ]);

        $this->assertEquals('', $subscriber->full_name);
    }

    public function test_subscriber_is_active_accessor(): void
    {
        $activeSubscriber = Subscriber::factory()->active()->create();
        $inactiveSubscriber = Subscriber::factory()->inactive()->create();

        $this->assertTrue($activeSubscriber->is_active);
        $this->assertFalse($inactiveSubscriber->is_active);
    }

    public function test_subscriber_is_unsubscribed_accessor(): void
    {
        $activeSubscriber = Subscriber::factory()->active()->create();
        $unsubscribedSubscriber = Subscriber::factory()->unsubscribed()->create();

        $this->assertFalse($activeSubscriber->is_unsubscribed);
        $this->assertTrue($unsubscribedSubscriber->is_unsubscribed);
    }

    public function test_subscriber_active_scope(): void
    {
        $activeSubscriber = Subscriber::factory()->active()->create();
        $inactiveSubscriber = Subscriber::factory()->inactive()->create();

        $activeSubscribers = Subscriber::active()->get();

        $this->assertCount(1, $activeSubscribers);
        $this->assertTrue($activeSubscribers->contains($activeSubscriber));
        $this->assertFalse($activeSubscribers->contains($inactiveSubscriber));
    }

    public function test_subscriber_inactive_scope(): void
    {
        $activeSubscriber = Subscriber::factory()->active()->create();
        $inactiveSubscriber = Subscriber::factory()->inactive()->create();

        $inactiveSubscribers = Subscriber::inactive()->get();

        $this->assertCount(1, $inactiveSubscribers);
        $this->assertFalse($inactiveSubscribers->contains($activeSubscriber));
        $this->assertTrue($inactiveSubscribers->contains($inactiveSubscriber));
    }

    public function test_subscriber_unsubscribed_scope(): void
    {
        $activeSubscriber = Subscriber::factory()->active()->create();
        $unsubscribedSubscriber = Subscriber::factory()->unsubscribed()->create();

        $unsubscribedSubscribers = Subscriber::unsubscribed()->get();

        $this->assertCount(1, $unsubscribedSubscribers);
        $this->assertFalse($unsubscribedSubscribers->contains($activeSubscriber));
        $this->assertTrue($unsubscribedSubscribers->contains($unsubscribedSubscriber));
    }

    public function test_subscriber_by_source_scope(): void
    {
        $websiteSubscriber = Subscriber::factory()->fromSource('website')->create();
        $adminSubscriber = Subscriber::factory()->fromSource('admin')->create();

        $websiteSubscribers = Subscriber::bySource('website')->get();

        $this->assertCount(1, $websiteSubscribers);
        $this->assertTrue($websiteSubscribers->contains($websiteSubscriber));
        $this->assertFalse($websiteSubscribers->contains($adminSubscriber));
    }

    public function test_subscriber_with_interests_scope(): void
    {
        $subscriber1 = Subscriber::factory()->withInterests(['products', 'news'])->create();
        $subscriber2 = Subscriber::factory()->withInterests(['events', 'blog'])->create();

        $productSubscribers = Subscriber::withInterests(['products'])->get();

        $this->assertCount(1, $productSubscribers);
        $this->assertTrue($productSubscribers->contains($subscriber1));
        $this->assertFalse($productSubscribers->contains($subscriber2));
    }

    public function test_subscriber_recent_scope(): void
    {
        $recentSubscriber = Subscriber::factory()->recent(7)->create();
        $oldSubscriber = Subscriber::factory()->create([
            'subscribed_at' => now()->subDays(30),
        ]);

        $recentSubscribers = Subscriber::recent(7)->get();

        $this->assertCount(1, $recentSubscribers);
        $this->assertTrue($recentSubscribers->contains($recentSubscriber));
        $this->assertFalse($recentSubscribers->contains($oldSubscriber));
    }

    public function test_subscriber_unsubscribe_method(): void
    {
        $subscriber = Subscriber::factory()->active()->create();

        $this->assertTrue($subscriber->unsubscribe());
        $this->assertEquals('unsubscribed', $subscriber->fresh()->status);
        $this->assertNotNull($subscriber->fresh()->unsubscribed_at);
    }

    public function test_subscriber_resubscribe_method(): void
    {
        $subscriber = Subscriber::factory()->unsubscribed()->create();

        $this->assertTrue($subscriber->resubscribe());
        $this->assertEquals('active', $subscriber->fresh()->status);
        $this->assertNull($subscriber->fresh()->unsubscribed_at);
    }

    public function test_subscriber_increment_email_count_method(): void
    {
        $subscriber = Subscriber::factory()->create(['email_count' => 5]);

        $this->assertTrue($subscriber->incrementEmailCount());
        $this->assertEquals(6, $subscriber->fresh()->email_count);
        $this->assertNotNull($subscriber->fresh()->last_email_sent_at);
    }

    public function test_subscriber_add_interest_method(): void
    {
        $subscriber = Subscriber::factory()->create(['interests' => ['products']]);

        $this->assertTrue($subscriber->addInterest('news'));
        $this->assertTrue($subscriber->fresh()->hasInterest('news'));
        $this->assertTrue($subscriber->fresh()->hasInterest('products'));
    }

    public function test_subscriber_add_interest_duplicate(): void
    {
        $subscriber = Subscriber::factory()->create(['interests' => ['products']]);

        $this->assertTrue($subscriber->addInterest('products')); // Should not add duplicate
        $this->assertCount(1, $subscriber->fresh()->interests);
    }

    public function test_subscriber_remove_interest_method(): void
    {
        $subscriber = Subscriber::factory()->create(['interests' => ['products', 'news']]);

        $this->assertTrue($subscriber->removeInterest('products'));
        $this->assertFalse($subscriber->fresh()->hasInterest('products'));
        $this->assertTrue($subscriber->fresh()->hasInterest('news'));
    }

    public function test_subscriber_has_interest_method(): void
    {
        $subscriber = Subscriber::factory()->create(['interests' => ['products', 'news']]);

        $this->assertTrue($subscriber->hasInterest('products'));
        $this->assertTrue($subscriber->hasInterest('news'));
        $this->assertFalse($subscriber->hasInterest('events'));
    }

    public function test_subscriber_static_subscribe_method(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $subscriber = Subscriber::subscribe([
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertEquals('test@example.com', $subscriber->email);
        $this->assertEquals('active', $subscriber->status);
        $this->assertEquals($user->id, $subscriber->user_id);
        $this->assertNotNull($subscriber->subscribed_at);
    }

    public function test_subscriber_static_subscribe_without_existing_user(): void
    {
        $subscriber = Subscriber::subscribe([
            'email' => 'new@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertEquals('new@example.com', $subscriber->email);
        $this->assertNull($subscriber->user_id);
    }

    public function test_subscriber_static_find_by_email(): void
    {
        $subscriber = Subscriber::factory()->create(['email' => 'test@example.com']);

        $foundSubscriber = Subscriber::findByEmail('test@example.com');
        $notFoundSubscriber = Subscriber::findByEmail('notfound@example.com');

        $this->assertInstanceOf(Subscriber::class, $foundSubscriber);
        $this->assertEquals($subscriber->id, $foundSubscriber->id);
        $this->assertNull($notFoundSubscriber);
    }

    public function test_subscriber_static_get_active_count(): void
    {
        Subscriber::factory()->active()->count(3)->create();
        Subscriber::factory()->inactive()->count(2)->create();

        $activeCount = Subscriber::getActiveCount();

        $this->assertEquals(3, $activeCount);
    }

    public function test_subscriber_static_get_recent_subscribers(): void
    {
        $recentSubscribers = Subscriber::factory()->recent(7)->count(2)->create();
        $oldSubscribers = Subscriber::factory()->create([
            'subscribed_at' => now()->subDays(30),
        ]);

        $recent = Subscriber::getRecentSubscribers(7);

        $this->assertCount(2, $recent);
        $this->assertTrue($recent->contains($recentSubscribers->first()));
        $this->assertTrue($recent->contains($recentSubscribers->last()));
        $this->assertFalse($recent->contains($oldSubscribers));
    }

    public function test_subscriber_boot_method_sets_subscribed_at(): void
    {
        $subscriber = Subscriber::factory()->create(['subscribed_at' => null]);

        $this->assertNotNull($subscriber->subscribed_at);
    }

    public function test_subscriber_boot_method_preserves_existing_subscribed_at(): void
    {
        $customDate = now()->subDays(10);
        $subscriber = Subscriber::factory()->create(['subscribed_at' => $customDate]);

        $this->assertEquals($customDate->format('Y-m-d H:i:s'), $subscriber->subscribed_at->format('Y-m-d H:i:s'));
    }
}
