<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralCampaignResource\Pages\ListReferralCampaigns;
use App\Filament\Resources\ReferralCampaigns\ReferralCampaignResource;
use App\Models\ReferralCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();

    $this->admin = User::factory()->create([
        'email'    => 'referral-campaigns-admin@example.test',
        'is_admin' => true,
    ]);

    $this->actingAs($this->admin);
});

it('shows active campaign records in the compatibility list page', function (): void {
    $active = ReferralCampaign::factory()->active()->create();

    Livewire::actingAs($this->admin)
        ->test(ListReferralCampaigns::class)
        ->assertCanSeeTableRecords([$active]);
});

it('does not register campaign resource in sidebar navigation', function (): void {
    expect(ReferralCampaignResource::shouldRegisterNavigation())->toBeFalse();
});
