<?php

declare(strict_types=1);

use App\Livewire\EmailMarketingManager;
use App\Models\Subscriber;
use App\Models\Company;
use Livewire\Livewire;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Mock HTTP requests for Mailchimp API
    Http::fake([
        'mailchimp.com/*' => Http::response([
            'stats' => [
                'member_count' => 150,
                'unsubscribe_count' => 5,
                'cleaned_count' => 2,
                'pending_count' => 3,
            ],
        ], 200),
    ]);
});

test('email marketing manager component loads successfully', function () {
    $component = Livewire::test(EmailMarketingManager::class);
    
    $component->assertStatus(200);
});

test('email marketing manager loads mailchimp stats on mount', function () {
    $component = Livewire::test(EmailMarketingManager::class);
    
    expect($component->mailchimpStats)->toBeArray()
        ->and($component->showStats)->toBeTrue()
        ->and($component->mailchimpStats['total_subscribers'])->toBe(150);
});

test('email marketing manager can sync all subscribers', function () {
    // Create test subscribers
    Subscriber::factory()->count(3)->create(['status' => 'active']);
    
    // Mock successful sync responses
    Http::fake([
        'mailchimp.com/3.0/lists/*/members' => Http::response([
            'id' => 'test-member-id',
            'email_address' => 'test@example.com',
            'status' => 'subscribed',
        ], 201),
    ]);

    $component = Livewire::test(EmailMarketingManager::class);
    
    $component->call('syncAllSubscribers');
    
    expect($component->syncResults)->toBeArray()
        ->and($component->syncResults['success'])->toBe(3)
        ->and($component->syncResults['failed'])->toBe(0);
    
    $component->assertSessionHas('success');
});

test('email marketing manager validates campaign form', function () {
    $component = Livewire::test(EmailMarketingManager::class);
    
    $component->call('createCampaign');
    
    $component->assertHasErrors([
        'campaignTitle' => 'required',
        'campaignSubject' => 'required',
    ]);
});

test('email marketing manager can create campaign', function () {
    // Mock campaign creation response
    Http::fake([
        'mailchimp.com/3.0/campaigns' => Http::response([
            'id' => 'test-campaign-id',
            'type' => 'regular',
            'settings' => [
                'subject_line' => 'Test Campaign',
                'title' => 'Test Campaign Title',
            ],
        ], 201),
    ]);

    $component = Livewire::test(EmailMarketingManager::class);
    
    $component->set('campaignTitle', 'Test Campaign Title')
        ->set('campaignSubject', 'Test Campaign Subject')
        ->set('fromName', 'Test Company')
        ->set('replyTo', 'test@example.com')
        ->call('createCampaign');
    
    $component->assertSessionHas('success');
});

test('email marketing manager can create interest segment', function () {
    // Mock segment creation response
    Http::fake([
        'mailchimp.com/3.0/lists/*/segments' => Http::response([
            'id' => 'test-segment-id',
            'name' => 'Products Subscribers',
            'member_count' => 25,
        ], 201),
    ]);

    $component = Livewire::test(EmailMarketingManager::class);
    
    $component->call('createInterestSegment', 'products');
    
    $component->assertSessionHas('success');
});

test('email marketing manager provides interests property', function () {
    $component = Livewire::test(EmailMarketingManager::class);
    
    $interests = $component->instance()->interests;
    
    expect($interests)->toBeArray()
        ->and($interests)->toHaveKey('products')
        ->and($interests)->toHaveKey('news')
        ->and($interests)->toHaveKey('promotions');
});

test('email marketing manager provides companies property', function () {
    // Create test companies
    Company::factory()->count(3)->create(['is_active' => true]);
    Company::factory()->create(['is_active' => false]); // Should not be included
    
    $component = Livewire::test(EmailMarketingManager::class);
    
    $companies = $component->instance()->companies;
    
    expect($companies)->toHaveCount(3)
        ->and($companies->every(fn($company) => $company->is_active))->toBeTrue();
});

test('email marketing manager handles sync errors gracefully', function () {
    Subscriber::factory()->create(['status' => 'active']);
    
    // Mock HTTP error response
    Http::fake([
        'mailchimp.com/*' => Http::response([], 500),
    ]);

    $component = Livewire::test(EmailMarketingManager::class);
    
    $component->call('syncAllSubscribers');
    
    $component->assertSessionHas('error');
});

test('email marketing manager handles campaign creation errors', function () {
    // Mock campaign creation error
    Http::fake([
        'mailchimp.com/3.0/campaigns' => Http::response([], 400),
    ]);

    $component = Livewire::test(EmailMarketingManager::class);
    
    $component->set('campaignTitle', 'Test Campaign Title')
        ->set('campaignSubject', 'Test Campaign Subject')
        ->set('fromName', 'Test Company')
        ->set('replyTo', 'test@example.com')
        ->call('createCampaign');
    
    $component->assertSessionHas('error');
});

test('email marketing manager can refresh stats', function () {
    $component = Livewire::test(EmailMarketingManager::class);
    
    // Clear initial stats
    $component->set('mailchimpStats', []);
    $component->set('showStats', false);
    
    $component->call('refreshStats');
    
    expect($component->mailchimpStats)->toBeArray()
        ->and($component->showStats)->toBeTrue()
        ->and($component->mailchimpStats['total_subscribers'])->toBe(150);
});

test('email marketing manager resets form after successful campaign creation', function () {
    // Mock successful campaign creation
    Http::fake([
        'mailchimp.com/3.0/campaigns' => Http::response([
            'id' => 'test-campaign-id',
        ], 201),
    ]);

    $component = Livewire::test(EmailMarketingManager::class);
    
    $component->set('campaignTitle', 'Test Campaign Title')
        ->set('campaignSubject', 'Test Campaign Subject')
        ->set('fromName', 'Test Company')
        ->set('replyTo', 'test@example.com')
        ->set('selectedInterest', 'products')
        ->call('createCampaign');
    
    expect($component->campaignTitle)->toBe('')
        ->and($component->campaignSubject)->toBe('')
        ->and($component->selectedInterest)->toBe('');
});

test('email marketing manager shows loading state during sync', function () {
    Subscriber::factory()->create(['status' => 'active']);
    
    // Mock slow response
    Http::fake([
        'mailchimp.com/3.0/lists/*/members' => Http::response([
            'id' => 'test-member-id',
        ], 201),
    ]);

    $component = Livewire::test(EmailMarketingManager::class);
    
    $component->call('syncAllSubscribers');
    
    expect($component->isSyncing)->toBeFalse(); // Should be false after completion
});

test('email marketing manager handles interest segment creation errors', function () {
    // Mock segment creation error
    Http::fake([
        'mailchimp.com/3.0/lists/*/segments' => Http::response([], 400),
    ]);

    $component = Livewire::test(EmailMarketingManager::class);
    
    $component->call('createInterestSegment', 'products');
    
    $component->assertSessionHas('error');
});
