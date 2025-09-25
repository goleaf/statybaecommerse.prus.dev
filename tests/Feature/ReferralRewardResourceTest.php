<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReferralRewardResourceTest extends TestCase
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

    public function test_can_list_referral_rewards(): void
    {
        $referralReward = ReferralReward::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards::class)
            ->assertCanSeeTableRecords([$referralReward]);
    }

    public function test_can_create_referral_reward(): void
    {
        $user = User::factory()->create();
        $referral = Referral::factory()->create(['referrer_id' => $user->id]);

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\CreateReferralReward::class)
            ->fillForm([
                'referral_id' => $referral->id,
                'user_id' => $user->id,
                'type' => 'discount',
                'amount' => 10.0,
                'currency_code' => 'EUR',
                'status' => 'pending',
                'title' => 'Test Reward',
                'description' => 'Test Description',
                'is_active' => true,
                'priority' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_rewards', [
            'referral_id' => $referral->id,
            'user_id' => $user->id,
            'type' => 'discount',
            'amount' => 10.0,
            'currency_code' => 'EUR',
            'status' => 'pending',
            'is_active' => true,
            'priority' => 1,
        ]);

        // Assert translatable fields stored for default locale
        $this->assertDatabaseHas('referral_rewards', [
            'title->lt' => 'Test Reward',
            'description->lt' => 'Test Description',
        ]);
    }

    public function test_can_edit_referral_reward(): void
    {
        $referralReward = ReferralReward::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\EditReferralReward::class, [
            'record' => $referralReward->getRouteKey(),
        ])
            ->fillForm([
                'title' => 'Updated Title',
                'description' => 'Updated Description',
                'amount' => 20.0,
                'type' => 'discount',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $referralReward->refresh();

        $this->assertEquals('Updated Title', $referralReward->getTranslation('title', 'lt'));
        $this->assertEquals('Updated Description', $referralReward->getTranslation('description', 'lt'));
        $this->assertEquals(20.0, $referralReward->amount);
        $this->assertEquals('discount', $referralReward->type);
    }

    public function test_can_view_referral_reward(): void
    {
        $referralReward = ReferralReward::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\ViewReferralReward::class, [
            'record' => $referralReward->getRouteKey(),
        ])
            ->assertSee($referralReward->getTranslation('title', 'lt'))
            ->assertSee($referralReward->getTranslation('description', 'lt'));
    }

    public function test_can_delete_referral_reward(): void
    {
        $referralReward = ReferralReward::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards::class)
            ->callTableAction('delete', $referralReward);

        $this->assertSoftDeleted('referral_rewards', [
            'id' => $referralReward->id,
        ]);
    }

    public function test_can_filter_by_status(): void
    {
        $pendingReward = ReferralReward::factory()->create(['status' => 'pending']);
        $appliedReward = ReferralReward::factory()->create(['status' => 'applied']);

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards::class)
            ->filterTable('status', 'pending')
            ->assertCanSeeTableRecords([$pendingReward])
            ->assertCanNotSeeTableRecords([$appliedReward]);
    }

    public function test_can_filter_by_type(): void
    {
        $discountReward = ReferralReward::factory()->create(['type' => 'discount']);
        $creditReward = ReferralReward::factory()->create(['type' => 'credit']);

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards::class)
            ->filterTable('type', 'discount')
            ->assertCanSeeTableRecords([$discountReward])
            ->assertCanNotSeeTableRecords([$creditReward]);
    }

    public function test_can_filter_by_active_status(): void
    {
        $activeReward = ReferralReward::factory()->create(['is_active' => true]);
        $inactiveReward = ReferralReward::factory()->create(['is_active' => false]);

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$activeReward])
            ->assertCanNotSeeTableRecords([$inactiveReward]);
    }

    public function test_can_apply_reward_action(): void
    {
        $referralReward = ReferralReward::factory()->create(['status' => 'pending']);

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards::class)
            ->callTableAction('apply', $referralReward);

        $referralReward->refresh();

        $this->assertEquals('applied', $referralReward->status);
        $this->assertNotNull($referralReward->applied_at);
    }

    public function test_can_expire_reward_action(): void
    {
        $referralReward = ReferralReward::factory()->create(['status' => 'pending']);

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards::class)
            ->callTableAction('expire', $referralReward);

        $referralReward->refresh();

        $this->assertEquals('expired', $referralReward->status);
    }

    public function test_can_bulk_apply_rewards(): void
    {
        $rewards = ReferralReward::factory()->count(3)->create(['status' => 'pending']);

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards::class)
            ->callTableBulkAction('apply', $rewards);

        foreach ($rewards as $reward) {
            $reward->refresh();
            $this->assertEquals('applied', $reward->status);
        }
    }

    public function test_can_bulk_expire_rewards(): void
    {
        $rewards = ReferralReward::factory()->count(3)->create(['status' => 'pending']);

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards::class)
            ->callTableBulkAction('expire', $rewards);

        foreach ($rewards as $reward) {
            $reward->refresh();
            $this->assertEquals('expired', $reward->status);
        }
    }

    public function test_can_search_referral_rewards(): void
    {
        $reward1 = ReferralReward::factory()->create(['title' => ['lt' => 'Test Reward 1']]);
        $reward2 = ReferralReward::factory()->create(['title' => ['lt' => 'Another Reward']]);

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards::class)
            ->searchTable('Test')
            ->assertCanSeeTableRecords([$reward1])
            ->assertCanNotSeeTableRecords([$reward2]);
    }

    public function test_can_sort_referral_rewards(): void
    {
        $reward1 = ReferralReward::factory()->create(['title' => ['lt' => 'A Reward']]);
        $reward2 = ReferralReward::factory()->create(['title' => ['lt' => 'B Reward']]);

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards::class)
            ->sortTable('title')
            ->assertCanSeeTableRecords([$reward1, $reward2], inOrder: true);
    }

    public function test_form_validation_works(): void
    {
        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\CreateReferralReward::class)
            ->fillForm([
                'title' => '',  // Required field
                'amount' => 'invalid',  // Must be numeric
            ])
            ->call('create')
            ->assertHasFormErrors(['title', 'amount']);
    }

    public function test_relationships_are_loaded(): void
    {
        $user = User::factory()->create();
        $referral = Referral::factory()->create(['referrer_id' => $user->id]);
        $order = Order::factory()->create();
        $referralReward = ReferralReward::factory()->create([
            'referral_id' => $referral->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
        ]);

        Livewire::test(\App\Filament\Resources\ReferralRewardResource\Pages\ViewReferralReward::class, [
            'record' => $referralReward->getRouteKey(),
        ])
            ->assertSee($user->name)
            ->assertSee($referral->referral_code)
            ->assertSee((string) $order->id);
    }
}
