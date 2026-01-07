<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Disable authorization bypass for testing
    config()->set('authorization.testing.skip_checks', false);
    
    // Seed the authorization system
    $this->seed(\Database\Seeders\AdminAuthorizationSeeder::class);
});

it('debugs admin panel access', function (): void {
    $user = User::factory()->create(['is_admin' => true]);
    
    // Check if user can access panel
    $panel = \Filament\Facades\Filament::getPanel('admin');
    $canAccess = $user->canAccessPanel($panel);
    
    dump('User is_admin: ' . ($user->is_admin ? 'true' : 'false'));
    dump('Can access panel: ' . ($canAccess ? 'true' : 'false'));
    
    $this->actingAs($user);
    
    // Try accessing dashboard directly
    $dashboardResponse = $this->get('/admin/dashboard');
    dump('Dashboard response status: ' . $dashboardResponse->status());
    
    $response = $this->get('/admin');
    
    dump('Response status: ' . $response->status());
    dump('Response headers: ', $response->headers->all());
    
    expect($canAccess)->toBeTrue();
});