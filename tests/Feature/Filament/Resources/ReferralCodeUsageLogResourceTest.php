<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralCodeUsageLogResource\Pages\ListReferralCodeUsageLogs;
use App\Models\ReferralCode;
use App\Models\ReferralCodeUsageLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralCodeUsageLogResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the Filament admin panel so resource routes and middleware are registered for the test run.
        $this->resolveAdminPanel();

        // Promote a dedicated administrator to authenticate each Livewire interaction under test.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_renders_usage_logs(): void
    {
        // Establish a deterministic referral code so the table has a concrete relationship to display.
        $referralOwner = User::factory()->create([
            'email' => 'referral.owner@example.com',
        ]);

        $referralCode = ReferralCode::factory()->for($referralOwner)->create([
            'code' => 'COVERAGECODE',
        ]);

        // Seed a usage log entry with explicit network metadata to verify the listing renders the record.
        $usageLog = ReferralCodeUsageLog::factory()
            ->for($referralCode, 'referralCode')
            ->for(User::factory()->create(['email' => 'usage.viewer@example.com']))
            ->create([
                'ip_address' => '203.0.113.5',
                'user_agent' => 'FilamentCoverage/1.0',
                'referrer'   => 'https://example.test/landing',
            ]);

        Livewire::test(ListReferralCodeUsageLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$usageLog]);
    }
}
