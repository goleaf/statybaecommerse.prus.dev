<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralCodeUsageLogResource\Pages\ListReferralCodeUsageLogs;
use App\Filament\Resources\ReferralRewardLogResource\Pages\ListReferralRewardLogs;
use App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards;
use App\Models\ReferralCode;
use App\Models\ReferralCodeUsageLog;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Quick smoke tests ensuring each referral-related Filament list page renders at least one record.
 */
final class ReferralResourcesSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament resolves the admin panel configuration before interacting with Livewire components.
        $this->resolveAdminPanel();

        // Normalise locales so the factories emit deterministic English translations for assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate as the default admin user so all resource policies allow list page access.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    /**
     * @return array<string, array{0: class-string, 1: string}>
     */
    public static function referralResourceProvider(): array
    {
        // Map each list page class to the helper responsible for seeding a visible record.
        return [
            'referral rewards'      => [ListReferralRewards::class, 'createReferralRewardRecord'],
            'referral reward logs'  => [ListReferralRewardLogs::class, 'createReferralRewardLogRecord'],
            'referral code usages'  => [ListReferralCodeUsageLogs::class, 'createReferralCodeUsageLogRecord'],
        ];
    }

    /**
     * @dataProvider referralResourceProvider
     */
    public function test_referral_list_pages_render_seed_records(string $pageClass, string $factoryMethod): void
    {
        // Use the dedicated helper to ensure the table has a concrete record for the current resource.
        $record = $this->{$factoryMethod}();

        // Hydrate the table data prior to asserting visibility so deferred queries execute in Filament v4.
        Livewire::test($pageClass)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$record]);
    }

    private function createReferralRewardRecord(): ReferralReward
    {
        // Generate a reward with deterministic copy so table assertions remain stable across locales.
        return ReferralReward::factory()->create([
            'title' => [
                'en' => 'Coverage Referral Reward',
                'lt' => 'Padengimo atlygis',
            ],
            'description' => [
                'en' => 'Awarded for coverage tests',
                'lt' => 'Suteikiamas testavimo metu',
            ],
            'status' => 'pending',
        ]);
    }

    private function createReferralRewardLogRecord(): ReferralRewardLog
    {
        // Provision a reward log tied to a known reward and user so relation columns resolve cleanly.
        $reward = $this->createReferralRewardRecord();
        $member = User::factory()->create(['name' => 'Referral Member']);

        return ReferralRewardLog::factory()->create([
            'referral_reward_id' => $reward->getKey(),
            'user_id'            => $member->getKey(),
            'action'             => ReferralRewardLog::ACTION_EARNED,
            'ip_address'         => '198.51.100.42',
        ]);
    }

    private function createReferralCodeUsageLogRecord(): ReferralCodeUsageLog
    {
        // Seed a referral code and user so the usage log can expose both relationships in the table output.
        $referralCode = ReferralCode::factory()->create(['code' => 'COVERAGE2024']);
        $member = User::factory()->create(['name' => 'Usage Member']);

        return ReferralCodeUsageLog::factory()
            ->withReferralCode($referralCode)
            ->withUser($member)
            ->create([
                'ip_address' => '203.0.113.77',
                'user_agent' => 'StatybaBot/1.0',
            ]);
    }
}
