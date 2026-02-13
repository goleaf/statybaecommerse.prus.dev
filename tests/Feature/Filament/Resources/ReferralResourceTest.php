<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralCampaigns\ReferralCampaignResource;
use App\Filament\Resources\ReferralCodes\ReferralCodeResource;
use App\Filament\Resources\ReferralCodeStatistics\ReferralCodeStatisticsResource;
use App\Filament\Resources\ReferralCodeUsageLogs\ReferralCodeUsageLogResource;
use App\Filament\Resources\ReferralRewardLogs\ReferralRewardLogResource;
use App\Filament\Resources\ReferralRewards\ReferralRewardResource;
use App\Filament\Resources\Referrals\Pages\EditReferral;
use App\Filament\Resources\Referrals\ReferralResource;
use App\Filament\Resources\Referrals\RelationManagers\ReferralCampaignsRelationManager;
use App\Filament\Resources\Referrals\RelationManagers\ReferralCodesRelationManager;
use App\Filament\Resources\Referrals\RelationManagers\ReferralCodeStatisticsRelationManager;
use App\Filament\Resources\Referrals\RelationManagers\ReferralCodeUsageLogsRelationManager;
use App\Filament\Resources\Referrals\RelationManagers\ReferralRewardLogsRelationManager;
use App\Filament\Resources\Referrals\RelationManagers\ReferralRewardsRelationManager;
use App\Filament\Resources\Referrals\RelationManagers\ReferralStatisticsRelationManager;
use App\Filament\Resources\ReferralStatistics\ReferralStatisticsResource;
use App\Models\Referral;
use App\Models\ReferralCampaign;
use App\Models\ReferralCode;
use App\Models\ReferralCodeStatistics;
use App\Models\ReferralCodeUsageLog;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\ReferralStatistics;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();

    $this->admin = User::factory()->create([
        'email'    => 'referrals-admin@example.test',
        'is_admin' => true,
    ]);

    $this->actingAs($this->admin);
});

it('registers all referral related tabs on the unified referrals page', function (): void {
    expect(ReferralResource::getRelations())->toBe([
        ReferralCampaignsRelationManager::class,
        ReferralCodesRelationManager::class,
        ReferralCodeStatisticsRelationManager::class,
        ReferralCodeUsageLogsRelationManager::class,
        ReferralRewardsRelationManager::class,
        ReferralRewardLogsRelationManager::class,
        ReferralStatisticsRelationManager::class,
    ]);

    expect((new EditReferral)->hasCombinedRelationManagerTabsWithContent())->toBeTrue();
});

it('keeps secondary referral resources out of left navigation', function (): void {
    expect(ReferralCampaignResource::shouldRegisterNavigation())->toBeFalse();
    expect(ReferralCodeResource::shouldRegisterNavigation())->toBeFalse();
    expect(ReferralCodeStatisticsResource::shouldRegisterNavigation())->toBeFalse();
    expect(ReferralCodeUsageLogResource::shouldRegisterNavigation())->toBeFalse();
    expect(ReferralRewardResource::shouldRegisterNavigation())->toBeFalse();
    expect(ReferralRewardLogResource::shouldRegisterNavigation())->toBeFalse();
    expect(ReferralStatisticsResource::shouldRegisterNavigation())->toBeFalse();
});

it('exposes all related datasets through the unified referrals relationships', function (): void {
    $campaign = ReferralCampaign::factory()->active()->create();

    $code = ReferralCode::factory()->create([
        'user_id'     => $this->admin->id,
        'campaign_id' => $campaign->id,
        'code'        => 'UNIFIED-LT-001',
        'is_active'   => true,
        'expires_at'  => now()->addDays(30),
    ]);

    $referred = User::factory()->create();

    $referral = Referral::factory()->create([
        'referrer_id'   => $this->admin->id,
        'referred_id'   => $referred->id,
        'referral_code' => $code->code,
        'status'        => 'pending',
        'expires_at'    => now()->addDays(30),
    ]);

    ReferralCodeStatistics::factory()->create([
        'referral_code_id' => $code->id,
    ]);

    ReferralCodeUsageLog::factory()->create([
        'referral_code_id' => $code->id,
        'user_id'          => $referred->id,
    ]);

    $reward = ReferralReward::factory()->create([
        'referral_id' => $referral->id,
        'user_id'     => $this->admin->id,
    ]);

    ReferralRewardLog::factory()->create([
        'referral_reward_id' => $reward->id,
        'user_id'            => $this->admin->id,
    ]);

    ReferralStatistics::factory()->create([
        'user_id' => $this->admin->id,
    ]);

    expect($referral->codes()->count())->toBe(1);
    expect($referral->campaigns()->count())->toBe(1);
    expect($referral->codeStatistics()->count())->toBe(1);
    expect($referral->codeUsageLogs()->count())->toBe(1);
    expect($referral->rewards()->count())->toBe(1);
    expect($referral->rewardLogs()->count())->toBe(1);
    expect($referral->statistics()->count())->toBeGreaterThan(0);
});
