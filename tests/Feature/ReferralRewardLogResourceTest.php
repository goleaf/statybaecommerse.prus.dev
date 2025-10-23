<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ReferralRewardLogs\Pages\ListReferralRewardLogs;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralRewardLogResourceTest extends TestCase
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

    public function test_listing_handles_null_user_agent(): void
    {
        $referralReward = ReferralReward::factory()->create();
        $user = User::factory()->create();

        $log = ReferralRewardLog::factory()->create([
            'referral_reward_id' => $referralReward->id,
            'user_id'            => $user->id,
            'user_agent'         => null,
        ]);

        Livewire::test(ListReferralRewardLogs::class)
            ->assertCanSeeTableRecords([$log]);
    }
}
