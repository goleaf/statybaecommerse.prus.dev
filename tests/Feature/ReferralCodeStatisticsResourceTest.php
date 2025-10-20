<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ReferralCodeStatisticsResource\Pages\CreateReferralCodeStatistics;
use App\Filament\Resources\ReferralCodeStatisticsResource\Pages\EditReferralCodeStatistics;
use App\Filament\Resources\ReferralCodeStatisticsResource\Pages\ListReferralCodeStatistics;
use App\Filament\Resources\ReferralCodeStatisticsResource\Pages\ViewReferralCodeStatistics;
use App\Models\ReferralCode;
use App\Models\ReferralCodeStatistics;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralCodeStatisticsResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

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
        $records = ReferralCodeStatistics::factory()->count(3)->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralCodeStatistics::class)
            ->assertCanSeeTableRecords($records);
    }

    public function test_can_create_record(): void
    {
        $referralCode = ReferralCode::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(CreateReferralCodeStatistics::class)
            ->fillForm([
                'referral_code_id' => $referralCode->id,
                'date' => now()->toDateString(),
                'total_views' => 250,
                'total_clicks' => 125,
                'total_signups' => 60,
                'total_conversions' => 30,
                'total_revenue' => 450.75,
                'metadata' => ['source' => 'newsletter'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_code_statistics', [
            'referral_code_id' => $referralCode->id,
            'total_views' => 250,
            'total_clicks' => 125,
            'total_signups' => 60,
            'total_conversions' => 30,
            'total_revenue' => 450.75,
        ]);
    }

    public function test_can_view_record(): void
    {
        $record = ReferralCodeStatistics::factory()->create([
            'total_views' => 500,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ViewReferralCodeStatistics::class, ['record' => $record->id])
            ->assertSee((string) $record->total_views)
            ->assertSee($record->referralCode->code);
    }

    public function test_can_edit_record(): void
    {
        $record = ReferralCodeStatistics::factory()->create([
            'total_views' => 100,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(EditReferralCodeStatistics::class, ['record' => $record->id])
            ->fillForm([
                'referral_code_id' => (string) $record->referral_code_id,
                'total_views' => 750,
                'total_clicks' => 300,
                'total_signups' => 140,
                'total_conversions' => 70,
                'total_revenue' => 980.25,
                'metadata' => ['note' => 'updated'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_code_statistics', [
            'id' => $record->id,
            'total_views' => 750,
            'total_clicks' => 300,
            'total_signups' => 140,
            'total_conversions' => 70,
            'total_revenue' => 980.25,
        ]);
    }
}
