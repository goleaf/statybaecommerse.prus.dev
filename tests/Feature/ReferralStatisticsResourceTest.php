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

    public function test_can_list_referral_statistics(): void
    {
        $statistics = ReferralStatistics::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralStatistics::class)
            ->assertCanSeeTableRecords([$statistics]);
    }

    public function test_can_create_referral_statistics(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(CreateReferralStatistics::class)
            ->fillForm([
                'user_id' => $user->id,
                'date' => now()->toDateString(),
                'total_referrals' => 10,
                'completed_referrals' => 8,
                'pending_referrals' => 2,
                'total_rewards_earned' => 50.0,
                'total_discounts_given' => 25.0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_statistics', [
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'total_referrals' => 10,
            'completed_referrals' => 8,
            'pending_referrals' => 2,
            'total_rewards_earned' => 50.0,
            'total_discounts_given' => 25.0,
        ]);
    }

    public function test_can_edit_referral_statistics(): void
    {
        $statistics = ReferralStatistics::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(EditReferralStatistics::class, [
                'record' => $statistics->getRouteKey(),
            ])
            ->fillForm([
                'total_referrals' => 15,
                'completed_referrals' => 12,
                'pending_referrals' => 3,
                'total_rewards_earned' => 75.0,
                'total_discounts_given' => 40.0,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $statistics->refresh();

        $this->assertEquals(15, $statistics->total_referrals);
        $this->assertEquals(12, $statistics->completed_referrals);
        $this->assertEquals(3, $statistics->pending_referrals);
        $this->assertEquals(75.0, $statistics->total_rewards_earned);
        $this->assertEquals(40.0, $statistics->total_discounts_given);
    }

    public function test_can_view_referral_statistics(): void
    {
        $statistics = ReferralStatistics::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(ViewReferralStatistics::class, [
                'record' => $statistics->getRouteKey(),
            ])
            ->assertSee($statistics->user->name)
            ->assertSee($statistics->date->format('Y-m-d'));
    }

    public function test_can_delete_referral_statistics(): void
    {
        $statistics = ReferralStatistics::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralStatistics::class)
            ->callTableAction('delete', $statistics);

        $this->assertDatabaseMissing('referral_statistics', [
            'id' => $statistics->id,
        ]);
    }

    public function test_can_filter_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $statistics1 = ReferralStatistics::factory()->create(['user_id' => $user1->id]);
        $statistics2 = ReferralStatistics::factory()->create(['user_id' => $user2->id]);

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralStatistics::class)
            ->filterTable('user_id', $user1->id)
            ->assertCanSeeTableRecords([$statistics1])
            ->assertCanNotSeeTableRecords([$statistics2]);
    }

    public function test_can_filter_by_date_range(): void
    {
        $statistics1 = ReferralStatistics::factory()->create(['date' => '2024-01-01']);
        $statistics2 = ReferralStatistics::factory()->create(['date' => '2024-01-15']);
        $statistics3 = ReferralStatistics::factory()->create(['date' => '2024-02-01']);

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralStatistics::class)
            ->filterTable('date_range', [
                'from' => '2024-01-01',
                'until' => '2024-01-31',
            ])
            ->assertCanSeeTableRecords([$statistics1, $statistics2])
            ->assertCanNotSeeTableRecords([$statistics3]);
    }

    public function test_can_filter_by_has_referrals(): void
    {
        $statisticsWithReferrals = ReferralStatistics::factory()->create(['total_referrals' => 5]);
        $statisticsWithoutReferrals = ReferralStatistics::factory()->create(['total_referrals' => 0]);

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralStatistics::class)
            ->filterTable('has_referrals')
            ->assertCanSeeTableRecords([$statisticsWithReferrals])
            ->assertCanNotSeeTableRecords([$statisticsWithoutReferrals]);
    }

    public function test_can_filter_by_has_rewards(): void
    {
        $statisticsWithRewards = ReferralStatistics::factory()->create(['total_rewards_earned' => 10.0]);
        $statisticsWithoutRewards = ReferralStatistics::factory()->create(['total_rewards_earned' => 0.0]);

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralStatistics::class)
            ->filterTable('has_rewards')
            ->assertCanSeeTableRecords([$statisticsWithRewards])
            ->assertCanNotSeeTableRecords([$statisticsWithoutRewards]);
    }

    public function test_can_refresh_statistics_action(): void
    {
        $statistics = ReferralStatistics::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralStatistics::class)
            ->callTableAction('refresh_stats', $statistics)
            ->assertNotified();
    }

    public function test_can_bulk_refresh_statistics(): void
    {
        $statistics = ReferralStatistics::factory()->count(3)->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralStatistics::class)
            ->callTableBulkAction('refresh_all_stats', $statistics)
            ->assertNotified();
    }

    public function test_can_search_referral_statistics(): void
    {
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);
        $statistics1 = ReferralStatistics::factory()->create(['user_id' => $user1->id]);
        $statistics2 = ReferralStatistics::factory()->create(['user_id' => $user2->id]);

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralStatistics::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$statistics1])
            ->assertCanNotSeeTableRecords([$statistics2]);
    }

    public function test_can_sort_referral_statistics(): void
    {
        $statistics1 = ReferralStatistics::factory()->create(['date' => '2024-01-01']);
        $statistics2 = ReferralStatistics::factory()->create(['date' => '2024-01-02']);

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralStatistics::class)
            ->sortTable('date')
            ->assertCanSeeTableRecords([$statistics1, $statistics2], inOrder: true);
    }

    public function test_form_validation_works(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(CreateReferralStatistics::class)
            ->fillForm([
                'user_id' => '',
                'date' => '',
                'total_referrals' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['user_id', 'date', 'total_referrals']);
    }

    public function test_relationships_are_loaded(): void
    {
        $user = User::factory()->create();
        $statistics = ReferralStatistics::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($this->adminUser)
            ->test(ViewReferralStatistics::class, [
                'record' => $statistics->getRouteKey(),
            ])
            ->assertSee($user->name);
    }

    public function test_statistics_are_displayed_correctly(): void
    {
        $statistics = ReferralStatistics::factory()->create([
            'total_referrals' => 10,
            'completed_referrals' => 8,
            'pending_referrals' => 2,
            'total_rewards_earned' => 50.0,
            'total_discounts_given' => 25.0,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ViewReferralStatistics::class, [
                'record' => $statistics->getRouteKey(),
            ])
            ->assertSee('10')
            ->assertSee('8')
            ->assertSee('2');
    }
}
