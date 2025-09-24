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
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_usage_logs(): void
    {
        $referralCode = ReferralCode::factory()->create();
        $user = User::factory()->create();
        $log = ReferralCodeUsageLog::factory()->create([
            'referral_code_id' => $referralCode->id,
            'user_id' => $user->id,
        ]);

        Livewire::test(\App\Filament\Resources\ReferralCodeUsageLogResource\Pages\ListReferralCodeUsageLogs::class)
            ->assertCanSeeTableRecords([$log]);
    }
}
