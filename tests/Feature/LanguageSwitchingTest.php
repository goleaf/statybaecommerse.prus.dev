<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('frontend uses lithuanian as default language', function () {
    $response = $this->get('/');
    
    expect(app()->getLocale())->toBe('lt');
});

test('admin panel uses english as default language', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/admin');
    
    expect(app()->getLocale())->toBe('en');
});

test('language switching works for admin panel', function () {
    $user = User::factory()->create();
    
    // Switch to Lithuanian in admin panel
    $response = $this->actingAs($user)
        ->get('/lang/lt')
        ->assertRedirect();
    
    // Check that admin locale is stored in session
    expect(session('admin_locale'))->toBe('lt');
});

test('language switching works for frontend', function () {
    // Switch to English in frontend
    $response = $this->get('/lang/en')
        ->assertRedirect();
    
    // Check that locale is stored in session
    expect(session('locale'))->toBe('en');
});

test('language switcher translations exist', function () {
    app()->setLocale('en');
    expect(__('filament::layout.buttons.language_switcher.label'))->toBe('Select Language');
    
    app()->setLocale('lt');
    expect(__('filament::layout.buttons.language_switcher.label'))->toBe('Pasirinkti kalbą');
});