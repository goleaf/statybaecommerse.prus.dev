<?php

declare(strict_types=1);

use App\Livewire\NewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('subscribes a new email address and dispatches the subscriber event', function (): void {
    $component = Livewire::test(NewsletterSubscription::class);

    $component
        ->set('email', 'new-subscriber@example.test')
        ->set('first_name', 'New')
        ->set('last_name', 'Subscriber')
        ->set('company', 'Statyba')
        ->set('interests', ['promotions', 'news'])
        ->call('subscribe');

    $subscriber = Subscriber::query()->where('email', 'new-subscriber@example.test')->first();

    expect($subscriber)->not->toBeNull();
    expect($subscriber->status)->toBe('active');
    expect($subscriber->source)->toBe('website');
    expect(Subscriber::query()->count())->toBe(1);

    $dispatches = collect(data_get($component->effects, 'dispatches') ?? []);
    expect($dispatches->pluck('name'))->toContain('subscriber-added');

    expect($component->get('email'))->toBe('');
    expect($component->get('interests'))->toBe([]);
});

it('re-subscribes an existing unsubscribed subscriber', function (): void {
    $existing = Subscriber::factory()->unsubscribed()->create([
        'email'      => 'jane@example.test',
        'first_name' => 'Jane',
        'last_name'  => 'Doe',
        'status'     => 'unsubscribed',
    ]);

    $component = Livewire::test(NewsletterSubscription::class);

    $component
        ->set('email', 'jane@example.test')
        ->call('subscribe');

    expect($existing->fresh()->status)->toBe('active');

    $dispatches = collect(data_get($component->effects, 'dispatches') ?? []);
    expect($dispatches)->toBeEmpty();
});

it('provides an info message when the subscriber is already active', function (): void {
    Subscriber::factory()->active()->create([
        'email'      => 'active@example.test',
        'first_name' => 'Active',
        'last_name'  => 'Subscriber',
    ]);

    $component = Livewire::test(NewsletterSubscription::class);

    $component
        ->set('email', 'active@example.test')
        ->call('subscribe');

    expect(Subscriber::query()->where('email', 'active@example.test')->count())->toBe(1);

    $dispatches = collect(data_get($component->effects, 'dispatches') ?? []);
    expect($dispatches)->toBeEmpty();
});

it('validates the email address field before subscribing', function (): void {
    $component = Livewire::test(NewsletterSubscription::class);

    $component
        ->set('email', 'not-an-email')
        ->call('subscribe')
        ->assertHasErrors(['email' => 'email']);

    expect(Subscriber::query()->count())->toBe(0);
});
