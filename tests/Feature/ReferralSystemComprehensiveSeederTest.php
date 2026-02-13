<?php

declare(strict_types=1);

use App\Models\Referral;
use App\Models\ReferralCampaign;
use App\Models\ReferralCode;
use App\Models\ReferralCodeStatistics;
use App\Models\ReferralCodeUsageLog;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\ReferralStatistics;
use Database\Seeders\ReferralSystemComprehensiveSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds lithuanian referral data across all related referral tables', function (): void {
    (new ReferralSystemComprehensiveSeeder)->run();

    expect(ReferralCampaign::query()->count())->toBeGreaterThan(0);
    expect(ReferralCode::query()->count())->toBeGreaterThan(0);
    expect(Referral::query()->count())->toBeGreaterThan(0);
    expect(ReferralCodeStatistics::query()->count())->toBeGreaterThan(0);
    expect(ReferralCodeUsageLog::query()->count())->toBeGreaterThan(0);
    expect(ReferralReward::query()->count())->toBeGreaterThan(0);
    expect(ReferralRewardLog::query()->count())->toBeGreaterThan(0);
    expect(ReferralStatistics::query()->count())->toBeGreaterThan(0);

    $campaign = ReferralCampaign::query()->firstOrFail();
    expect($campaign->getTranslation('name', 'lt'))->not->toBe('');
    expect($campaign->metadata['rinka'] ?? null)->toBe('Lietuva');

    $referral = Referral::query()
        ->has('codes')
        ->has('campaigns')
        ->has('codeStatistics')
        ->has('codeUsageLogs')
        ->has('rewards')
        ->has('rewardLogs')
        ->first();

    expect($referral)->not->toBeNull();

    $ltStatisticCount = ReferralStatistics::query()
        ->get()
        ->filter(static fn (ReferralStatistics $statistics): bool => ($statistics->metadata['rinka'] ?? null) === 'LT')
        ->count();

    expect($ltStatisticCount)->toBeGreaterThan(0);
});
