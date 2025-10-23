<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ReferralCode;
use App\Models\ReferralCodeUsageLog;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralCodeUsageLogResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs(User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_usage_logs(): void
    {
        $referralCode = ReferralCode::factory()->create();
        $user = User::factory()->create();
        $log = ReferralCodeUsageLog::factory()->create([
            'referral_code_id' => $referralCode->id,
            'user_id'          => $user->id,
        ]);

        Livewire::test(\App\Filament\Resources\ReferralCodeUsageLogResource\Pages\ListReferralCodeUsageLogs::class)
            ->assertCanSeeTableRecords([$log]);
    }

    public function test_can_create_usage_log_with_metadata(): void
    {
        $referralCode = ReferralCode::factory()->create();
        $user = User::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralCodeUsageLogResource\Pages\CreateReferralCodeUsageLog::class)
            ->fillForm([
                'referral_code_id' => (string) $referralCode->getKey(),
                'user_id'          => (string) $user->getKey(),
                'ip_address'       => '198.51.100.10',
                'referrer'         => 'https://example.com',
                'user_agent'       => 'Mozilla/5.0',
                'metadata'         => [
                    'device'  => 'desktop',
                    'browser' => 'Firefox',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_code_usage_logs', [
            'referral_code_id' => $referralCode->id,
            'user_id'          => $user->id,
            'ip_address'       => '198.51.100.10',
            'referrer'         => 'https://example.com',
        ]);

        $log = ReferralCodeUsageLog::latest()->first();

        $this->assertSame([
            'device'  => 'desktop',
            'browser' => 'Firefox',
        ], $log->metadata);
    }

    public function test_can_filter_usage_logs_by_referral_code_and_user(): void
    {
        $referralCode = ReferralCode::factory()->create();
        $user = User::factory()->create();
        $matchingLog = ReferralCodeUsageLog::factory()->create([
            'referral_code_id' => $referralCode->id,
            'user_id'          => $user->id,
        ]);
        $otherLog = ReferralCodeUsageLog::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralCodeUsageLogResource\Pages\ListReferralCodeUsageLogs::class)
            ->filterTable('referral_code_id', $referralCode->id)
            ->filterTable('user_id', $user->id)
            ->assertCanSeeTableRecords([$matchingLog])
            ->assertCanNotSeeTableRecords([$otherLog]);
    }
}
