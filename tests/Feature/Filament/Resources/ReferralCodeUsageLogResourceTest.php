<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralCodeUsageLogResource\Pages\ListReferralCodeUsageLogs;
use App\Models\ReferralCode;
use App\Models\ReferralCodeUsageLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('lists referral code usage logs for administrators', function (): void {
    // Arrange: seed an admin plus the supporting referral code and log fixtures.
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $user = User::factory()->create([
        'name' => 'Usage Tester',
    ]);
    $referralCode = ReferralCode::factory()->for($user)->withCode('SHAREME')->create();
    $usageLog = ReferralCodeUsageLog::factory()
        ->withUser($user)
        ->withReferralCode($referralCode)
        ->create([
            'ip_address' => '198.51.100.24',
            'user_agent' => 'ReferralClient/2.0',
            'referrer' => 'https://example.test/campaign',
        ]);

    // Act: impersonate the admin to load the Livewire list component.
    actingAs($admin);

    // Assert: verify the hydrated table exposes the seeded usage log entry.
    Livewire::test(ListReferralCodeUsageLogs::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$usageLog]);
});

it('filters referral code usage logs by referral code', function (): void {
    // Arrange: prepare admin context and contrasting usage logs with distinct codes.
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $primaryCode = ReferralCode::factory()->withCode('INVITE1')->create();
    $secondaryCode = ReferralCode::factory()->withCode('INVITE2')->create();
    $matchingLog = ReferralCodeUsageLog::factory()->withReferralCode($primaryCode)->create([
        'ip_address' => '203.0.113.15',
    ]);
    $otherLog = ReferralCodeUsageLog::factory()->withReferralCode($secondaryCode)->create([
        'ip_address' => '203.0.113.16',
    ]);

    // Act: authenticate and apply the select filter to isolate the first referral code.
    actingAs($admin);

    Livewire::test(ListReferralCodeUsageLogs::class)
        ->call('loadTable')
        ->filterTable('referral_code_id', $primaryCode->getKey())
        ->call('loadTable')
        ->assertCanSeeTableRecords([$matchingLog])
        ->assertCanNotSeeTableRecords([$otherLog]);
});

it('searches referral code usage logs by ip address', function (): void {
    // Arrange: create a deterministic log entry whose IP will be used for search assertions.
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $searchableLog = ReferralCodeUsageLog::factory()->create([
        'ip_address' => '192.0.2.44',
    ]);
    $nonMatchingLog = ReferralCodeUsageLog::factory()->create([
        'ip_address' => '192.0.2.55',
    ]);

    // Act: sign in and perform the search query targeting the known IP address.
    actingAs($admin);

    Livewire::test(ListReferralCodeUsageLogs::class)
        ->call('loadTable')
        ->searchTable('192.0.2.44')
        ->call('loadTable')
        ->assertCanSeeTableRecords([$searchableLog])
        ->assertCanNotSeeTableRecords([$nonMatchingLog]);
});
