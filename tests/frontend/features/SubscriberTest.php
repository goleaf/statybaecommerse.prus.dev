<?php

declare(strict_types=1);

use App\Livewire\NewsletterSubscription;
use App\Models\Subscriber;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    // Clear any existing data before each test
    \App\Models\Subscriber::truncate();
    \App\Models\User::truncate();
});

test('can create subscriber', function () {
    $subscriberData = [
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'company' => 'Test Company',
        'interests' => ['products', 'news'],
        'source' => 'website',
        'status' => 'active',
    ];

    $subscriber = Subscriber::create($subscriberData);

    expect($subscriber->email)->toBe('test@example.com')
        ->and($subscriber->first_name)->toBe('John')
        ->and($subscriber->last_name)->toBe('Doe')
        ->and($subscriber->company)->toBe('Test Company')
        ->and($subscriber->interests)->toBe(['products', 'news'])
        ->and($subscriber->status)->toBe('active')
        ->and($subscriber->subscribed_at)->not->toBeNull();

    $this->assertDatabaseHas('subscribers', [
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'company' => 'Test Company',
        'status' => 'active',
    ]);
});

test('subscriber can be linked to user', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'name' => 'John Doe',
    ]);

    $subscriber = Subscriber::create([
        'user_id' => $user->id,
        'email' => 'user@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'status' => 'active',
    ]);

    expect($subscriber->user())->not->toBeNull()
        ->and($subscriber->user_id)->toBe($user->id);
});

test('subscribe static method creates subscriber', function () {
    $data = [
        'email' => 'subscribe@example.com',
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'company' => 'Smith Corp',
        'interests' => ['promotions'],
        'source' => 'website',
    ];

    $subscriber = Subscriber::subscribe($data);

    expect($subscriber->email)->toBe('subscribe@example.com')
        ->and($subscriber->status)->toBe('active')
        ->and($subscriber->subscribed_at)->not->toBeNull();
});

test('subscribe links to existing user', function () {
    $user = User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $subscriber = Subscriber::subscribe([
        'email' => 'existing@example.com',
        'first_name' => 'Existing',
        'last_name' => 'User',
    ]);

    expect($subscriber->user_id)->toBe($user->id);
});

test('can unsubscribe and resubscribe', function () {
    $subscriber = Subscriber::factory()->create(['status' => 'active']);

    $subscriber->unsubscribe();
    expect($subscriber->status)->toBe('unsubscribed')
        ->and($subscriber->unsubscribed_at)->not->toBeNull();

    $subscriber->resubscribe();
    expect($subscriber->status)->toBe('active')
        ->and($subscriber->unsubscribed_at)->toBeNull();
});

test('can manage interests', function () {
    $subscriber = Subscriber::factory()->create(['interests' => ['products']]);

    $subscriber->addInterest('news');
    expect($subscriber->interests)->toContain('news')
        ->and($subscriber->interests)->toContain('products');

    $subscriber->removeInterest('products');
    expect($subscriber->interests)->not->toContain('products')
        ->and($subscriber->interests)->toContain('news');

    expect($subscriber->hasInterest('news'))->toBeTrue()
        ->and($subscriber->hasInterest('products'))->toBeFalse();
});

test('can increment email count', function () {
    $subscriber = Subscriber::factory()->create(['email_count' => 5]);

    $subscriber->incrementEmailCount();
    expect($subscriber->email_count)->toBe(6);
});

test('scopes work correctly', function () {
    Subscriber::factory()->create(['status' => 'active']);
    Subscriber::factory()->create(['status' => 'inactive']);
    Subscriber::factory()->create(['status' => 'unsubscribed']);

    expect(Subscriber::active()->count())->toBe(1)
        ->and(Subscriber::inactive()->count())->toBe(1)
        ->and(Subscriber::unsubscribed()->count())->toBe(1);
});

test('recent scope works', function () {
    $recent = Subscriber::factory()->create(['subscribed_at' => now()->subDays(5)]);
    $old = Subscriber::factory()->create(['subscribed_at' => now()->subDays(15)]);

    $recentSubscribers = Subscriber::recent(10)->get();

    // Check that we have the right count and the recent subscriber is included
    expect($recentSubscribers->count())->toBe(1)
        ->and($recentSubscribers->first()->id)->toBe($recent->id);
});

test('newsletter subscription component works', function () {
    $component = Livewire::test(NewsletterSubscription::class)
        ->set('email', 'test@example.com')
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->set('company', 'Test Company')
        ->set('interests', ['products', 'news'])
        ->call('subscribe');

    // Check that subscriber was created
    $this->assertDatabaseHas('subscribers', [
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'company' => 'Test Company',
        'status' => 'active',
    ]);

    // Check that we have exactly one subscriber
    $this->assertDatabaseCount('subscribers', 1);
});

test('newsletter subscription validates email', function () {
    Livewire::test(NewsletterSubscription::class)
        ->set('email', 'invalid-email')
        ->call('subscribe')
        ->assertHasErrors(['email']);
});

test('newsletter subscription handles duplicate email', function () {
    Subscriber::factory()->create(['email' => 'existing@example.com', 'status' => 'active']);

    $component = Livewire::test(NewsletterSubscription::class)
        ->set('email', 'existing@example.com')
        ->call('subscribe');

    // Check that no new subscriber was created
    $this->assertDatabaseCount('subscribers', 1);

    // Check that the existing subscriber is still active
    $this->assertDatabaseHas('subscribers', [
        'email' => 'existing@example.com',
        'status' => 'active',
    ]);
});

test('newsletter subscription resubscribes unsubscribed', function () {
    $subscriber = Subscriber::factory()->create([
        'email' => 'unsubscribed@example.com',
        'status' => 'unsubscribed',
    ]);

    $component = Livewire::test(NewsletterSubscription::class)
        ->set('email', 'unsubscribed@example.com')
        ->call('subscribe');

    $subscriber->refresh();
    expect($subscriber->status)->toBe('active');

    // Check that the subscriber was resubscribed in the database
    $this->assertDatabaseHas('subscribers', [
        'email' => 'unsubscribed@example.com',
        'status' => 'active',
    ]);
});

test('full name accessor works', function () {
    $subscriber = Subscriber::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    expect($subscriber->full_name)->toBe('John Doe');
});

test('is active accessor works', function () {
    $activeSubscriber = Subscriber::factory()->create(['status' => 'active']);
    $inactiveSubscriber = Subscriber::factory()->create(['status' => 'inactive']);

    expect($activeSubscriber->is_active)->toBeTrue()
        ->and($inactiveSubscriber->is_active)->toBeFalse();
});

test('find by email static method works', function () {
    $subscriber = Subscriber::factory()->create(['email' => 'find@example.com']);

    $found = Subscriber::findByEmail('find@example.com');
    $notFound = Subscriber::findByEmail('notfound@example.com');

    expect($found->id)->toBe($subscriber->id)
        ->and($notFound)->toBeNull();
});

test('get active count static method works', function () {
    Subscriber::factory()->create(['status' => 'active']);
    Subscriber::factory()->create(['status' => 'active']);
    Subscriber::factory()->create(['status' => 'inactive']);

    expect(Subscriber::getActiveCount())->toBe(2);
});

test('get recent subscribers static method works', function () {
    $recent = Subscriber::factory()->create(['subscribed_at' => now()->subDays(5)]);
    $old = Subscriber::factory()->create(['subscribed_at' => now()->subDays(15)]);

    $recentSubscribers = Subscriber::getRecentSubscribers(10);

    // Check that we have the right count and the recent subscriber is included
    expect($recentSubscribers->count())->toBe(1)
        ->and($recentSubscribers->first()->id)->toBe($recent->id);
});
