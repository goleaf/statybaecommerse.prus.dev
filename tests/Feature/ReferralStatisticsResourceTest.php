<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ReferralStatisticsResource\Pages\CreateReferralStatistics;
use App\Filament\Resources\ReferralStatisticsResource\Pages\EditReferralStatistics;
use App\Filament\Resources\ReferralStatisticsResource\Pages\ListReferralStatistics;
use App\Filament\Resources\ReferralStatisticsResource\Pages\ViewReferralStatistics;
use App\Models\ReferralStatistics;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralStatisticsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_records(): void
    {
        $records = ReferralStatistics::factory()->count(3)->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralStatistics::class)
            ->assertCanSeeTableRecords($records);
    }

    public function test_can_create_record(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(CreateReferralStatistics::class)
            ->fillForm([
                'user_id' => $user->id,
                'date' => now()->toDateString(),
                'total_referrals' => 5,
                'completed_referrals' => 3,
                'pending_referrals' => 2,
                'total_rewards_earned' => 10.5,
                'total_discounts_given' => 4.0,
                'metadata' => ['source' => 'test'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount('referral_statistics', 1);
    }

    public function test_can_view_record(): void
    {
        $record = ReferralStatistics::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(ViewReferralStatistics::class, ['record' => $record->id])
            ->assertSee((string) $record->total_referrals);
    }

    public function test_can_edit_record(): void
    {
        $record = ReferralStatistics::factory()->create([
            'total_referrals' => 1,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(EditReferralStatistics::class, ['record' => $record->id])
            ->fillForm([
                'total_referrals' => 10,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_statistics', [
            'id' => $record->id,
            'total_referrals' => 10,
        ]);
    }
}
