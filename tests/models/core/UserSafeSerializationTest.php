<?php

declare(strict_types=1);

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\AdminUserResource;

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => 'secret123',
        'remember_token' => 'token123',
        'api_token' => 'api123',
        'two_factor_secret' => 'secret',
        'two_factor_recovery_codes' => ['code1', 'code2'],
        'verification_token' => 'verify123',
        'password_reset_token' => 'reset123',
        'stripe_customer_id' => 'cus_123',
        'stripe_account_id' => 'acct_123',
        'last_login_ip' => '192.168.1.1',
        'email' => 'test@example.com',
        'phone_number' => '+37012345678',
        'date_of_birth' => '1990-01-01',
        'preferences' => ['theme' => 'dark'],
        'privacy_settings' => ['public_profile' => false],
        'marketing_preferences' => ['newsletter' => true],
        'social_links' => ['facebook' => 'https://facebook.com/user'],
        'notification_preferences' => ['email' => true],
        'referral_code' => 'REF123',
        'subscription_status' => 'active',
        'subscription_plan' => 'premium',
    ]);
});

it('can get safe attributes excluding sensitive fields', function () {
    $safeAttributes = $this->user->getSafeAttributes();
    
    // Should exclude sensitive fields
    expect($safeAttributes)->not->toHaveKey('password');
    expect($safeAttributes)->not->toHaveKey('remember_token');
    expect($safeAttributes)->not->toHaveKey('api_token');
    expect($safeAttributes)->not->toHaveKey('two_factor_secret');
    expect($safeAttributes)->not->toHaveKey('two_factor_recovery_codes');
    expect($safeAttributes)->not->toHaveKey('verification_token');
    expect($safeAttributes)->not->toHaveKey('password_reset_token');
    expect($safeAttributes)->not->toHaveKey('stripe_customer_id');
    expect($safeAttributes)->not->toHaveKey('stripe_account_id');
    expect($safeAttributes)->not->toHaveKey('last_login_ip');
    
    // Should include safe fields
    expect($safeAttributes)->toHaveKey('id');
    expect($safeAttributes)->toHaveKey('name');
    expect($safeAttributes)->toHaveKey('email');
    expect($safeAttributes)->toHaveKey('phone_number');
    expect($safeAttributes)->toHaveKey('created_at');
    expect($safeAttributes)->toHaveKey('updated_at');
});

it('can get API safe attributes excluding more sensitive fields', function () {
    $apiSafeAttributes = $this->user->getApiSafeAttributes();
    
    // Should exclude all sensitive fields plus additional API-sensitive fields
    expect($apiSafeAttributes)->not->toHaveKey('password');
    expect($apiSafeAttributes)->not->toHaveKey('remember_token');
    expect($apiSafeAttributes)->not->toHaveKey('api_token');
    expect($apiSafeAttributes)->not->toHaveKey('email');
    expect($apiSafeAttributes)->not->toHaveKey('phone_number');
    expect($apiSafeAttributes)->not->toHaveKey('date_of_birth');
    expect($apiSafeAttributes)->not->toHaveKey('preferences');
    expect($apiSafeAttributes)->not->toHaveKey('privacy_settings');
    expect($apiSafeAttributes)->not->toHaveKey('marketing_preferences');
    expect($apiSafeAttributes)->not->toHaveKey('social_links');
    expect($apiSafeAttributes)->not->toHaveKey('notification_preferences');
    expect($apiSafeAttributes)->not->toHaveKey('referral_code');
    expect($apiSafeAttributes)->not->toHaveKey('subscription_status');
    expect($apiSafeAttributes)->not->toHaveKey('subscription_plan');
    
    // Should include safe fields
    expect($apiSafeAttributes)->toHaveKey('id');
    expect($apiSafeAttributes)->toHaveKey('name');
    expect($apiSafeAttributes)->toHaveKey('created_at');
    expect($apiSafeAttributes)->toHaveKey('updated_at');
});

it('can get admin safe attributes excluding only most sensitive fields', function () {
    $adminSafeAttributes = $this->user->getAdminSafeAttributes();
    
    // Should exclude only the most sensitive fields
    expect($adminSafeAttributes)->not->toHaveKey('password');
    expect($adminSafeAttributes)->not->toHaveKey('remember_token');
    expect($adminSafeAttributes)->not->toHaveKey('api_token');
    expect($adminSafeAttributes)->not->toHaveKey('two_factor_secret');
    expect($adminSafeAttributes)->not->toHaveKey('two_factor_recovery_codes');
    expect($adminSafeAttributes)->not->toHaveKey('verification_token');
    expect($adminSafeAttributes)->not->toHaveKey('password_reset_token');
    
    // Should include admin-relevant fields
    expect($adminSafeAttributes)->toHaveKey('id');
    expect($adminSafeAttributes)->toHaveKey('name');
    expect($adminSafeAttributes)->toHaveKey('email');
    expect($adminSafeAttributes)->toHaveKey('phone_number');
    expect($adminSafeAttributes)->toHaveKey('subscription_status');
    expect($adminSafeAttributes)->toHaveKey('subscription_plan');
    expect($adminSafeAttributes)->toHaveKey('created_at');
    expect($adminSafeAttributes)->toHaveKey('updated_at');
});

it('can use toSafeArray method', function () {
    $safeArray = $this->user->toSafeArray();
    
    expect($safeArray)->not->toHaveKey('password');
    expect($safeArray)->not->toHaveKey('remember_token');
    expect($safeArray)->toHaveKey('id');
    expect($safeArray)->toHaveKey('name');
});

it('can use toApiSafeArray method', function () {
    $apiSafeArray = $this->user->toApiSafeArray();
    
    expect($apiSafeArray)->not->toHaveKey('password');
    expect($apiSafeArray)->not->toHaveKey('email');
    expect($apiSafeArray)->toHaveKey('id');
    expect($apiSafeArray)->toHaveKey('name');
});

it('can use toAdminSafeArray method', function () {
    $adminSafeArray = $this->user->toAdminSafeArray();
    
    expect($adminSafeArray)->not->toHaveKey('password');
    expect($adminSafeArray)->toHaveKey('email');
    expect($adminSafeArray)->toHaveKey('id');
    expect($adminSafeArray)->toHaveKey('name');
});

it('can exclude additional fields when specified', function () {
    $safeAttributes = $this->user->getSafeAttributes(['email', 'phone_number']);
    
    expect($safeAttributes)->not->toHaveKey('password');
    expect($safeAttributes)->not->toHaveKey('email');
    expect($safeAttributes)->not->toHaveKey('phone_number');
    expect($safeAttributes)->toHaveKey('id');
    expect($safeAttributes)->toHaveKey('name');
});

it('can use except method directly', function () {
    $excludedAttributes = $this->user->except(['password', 'remember_token', 'api_token']);
    
    expect($excludedAttributes)->not->toHaveKey('password');
    expect($excludedAttributes)->not->toHaveKey('remember_token');
    expect($excludedAttributes)->not->toHaveKey('api_token');
    expect($excludedAttributes)->toHaveKey('id');
    expect($excludedAttributes)->toHaveKey('name');
    expect($excludedAttributes)->toHaveKey('email');
});

it('user resource excludes sensitive fields', function () {
    $resource = new UserResource($this->user);
    $resourceArray = $resource->toArray(request());
    
    // Should not contain sensitive fields
    expect($resourceArray)->not->toHaveKey('password');
    expect($resourceArray)->not->toHaveKey('remember_token');
    expect($resourceArray)->not->toHaveKey('api_token');
    expect($resourceArray)->not->toHaveKey('email');
    expect($resourceArray)->not->toHaveKey('phone_number');
    
    // Should contain safe fields
    expect($resourceArray)->toHaveKey('id');
    expect($resourceArray)->toHaveKey('name');
    expect($resourceArray)->toHaveKey('full_name');
    expect($resourceArray)->toHaveKey('initials');
});

it('admin user resource excludes only most sensitive fields', function () {
    $resource = new AdminUserResource($this->user);
    $resourceArray = $resource->toArray(request());
    
    // Should not contain most sensitive fields
    expect($resourceArray)->not->toHaveKey('password');
    expect($resourceArray)->not->toHaveKey('remember_token');
    expect($resourceArray)->not->toHaveKey('api_token');
    
    // Should contain admin-relevant fields
    expect($resourceArray)->toHaveKey('id');
    expect($resourceArray)->toHaveKey('name');
    expect($resourceArray)->toHaveKey('email');
    expect($resourceArray)->toHaveKey('phone_number');
    expect($resourceArray)->toHaveKey('subscription_status');
    expect($resourceArray)->toHaveKey('total_spent');
    expect($resourceArray)->toHaveKey('orders_count');
});
