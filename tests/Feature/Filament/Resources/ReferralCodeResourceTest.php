<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralCodeResource\Pages\ListReferralCodes;
use App\Filament\Resources\ReferralCodes\ReferralCodeResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();

    $this->admin = User::factory()->create([
        'email'    => 'info@egisstatyba.lt',
        'is_admin' => true,
    ]);

    $this->actingAs($this->admin);
});

it('keeps compatibility referral code pages registered', function (): void {
    $pages = ReferralCodeResource::getPages();

    expect($pages)->toHaveKeys(['index', 'create', 'edit']);
    expect(ListReferralCodes::class)->toBeString();
});

it('does not register referral code resource in sidebar navigation', function (): void {
    expect(ReferralCodeResource::shouldRegisterNavigation())->toBeFalse();
});
