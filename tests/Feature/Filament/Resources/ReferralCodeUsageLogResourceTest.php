<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralCodeUsageLogResource;
use App\Filament\Resources\ReferralCodeUsageLogResource\Pages\CreateReferralCodeUsageLog;
use App\Filament\Resources\ReferralCodeUsageLogResource\Pages\ListReferralCodeUsageLogs;
use App\Models\ReferralCode;
use App\Models\ReferralCodeUsageLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Log in a super admin to satisfy Filament authorization checks for every scenario.
    $this->admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $this->actingAs($this->admin);
});

it('feature: loads referral code usage log index page', function (): void {
    // Verify the Livewire listing boots correctly for administrators.
    $this
        ->get(ReferralCodeUsageLogResource::getUrl('index'))
        ->assertOk();
});

it('feature: loads referral code usage log creation page', function (): void {
    // Ensure the resource exposes the creation form without permission issues.
    $this
        ->get(ReferralCodeUsageLogResource::getUrl('create'))
        ->assertOk();
});

it('feature: creates a referral code usage log entry', function (): void {
    // Seed the related referral code and user to satisfy the form relationship requirements.
    $referralCode = ReferralCode::factory()->create(['code' => 'CODE-XYZ']);
    $actor = User::factory()->create(['name' => 'Usage Tracker']);

    // Submit the form payload with traceable metadata for deterministic assertions.
    Livewire::test(CreateReferralCodeUsageLog::class)
        ->fillForm([
            'referral_code_id' => $referralCode->getKey(),
            'user_id'          => $actor->getKey(),
            'ip_address'       => '198.51.100.24',
            'referrer'         => 'https://prus.dev/campaign',
            'user_agent'       => 'StatybaUsageBot/2.0',
            'metadata'         => json_encode(['utm_source' => 'newsletter']),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Confirm the stored log references the intended referral code and actor.
    $this->assertDatabaseHas('referral_code_usage_logs', [
        'referral_code_id' => $referralCode->getKey(),
        'user_id'          => $actor->getKey(),
        'ip_address'       => '198.51.100.24',
    ]);
});

it('feature: lists usage logs within the Filament table', function (): void {
    // Generate representative logs so the table component has material to render.
    $logs = ReferralCodeUsageLog::factory()->count(2)->create();

    // Ensure the Livewire table exposes the stored entries to the administrator.
    Livewire::test(ListReferralCodeUsageLogs::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords($logs);
});
