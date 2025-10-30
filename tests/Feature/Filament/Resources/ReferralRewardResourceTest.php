<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralRewardResource\Pages\CreateReferralReward;
use App\Filament\Resources\ReferralRewardResource\Pages\EditReferralReward;
use App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards;
use App\Filament\Resources\ReferralRewardResource\Pages\ViewReferralReward;
use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralRewardResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the Filament admin panel so Livewire components target the correct panel configuration.
        $this->resolveAdminPanel();

        // Authenticate as a baseline administrator who can bypass referral reward policies in the panel.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_existing_rewards(): void
    {
        // Create a reward entry so the listing table has a concrete record to render.
        $reward = ReferralReward::factory()->create();

        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$reward]);
    }

    public function test_create_page_persists_new_reward(): void
    {
        // Provision related models referenced by the creation form dropdowns.
        $referrer = User::factory()->create();
        $referral = Referral::factory()->create([
            'referrer_id' => $referrer->getKey(),
        ]);

        Livewire::test(CreateReferralReward::class)
            ->fillForm([
                'referral_id'   => $referral->getKey(),
                'user_id'       => $referrer->getKey(),
                'type'          => 'discount',
                'amount'        => 10.0,
                'currency_code' => 'EUR',
                'status'        => 'pending',
                'title'         => 'Launch Discount',
                'description'   => 'Reward issued for new referral sign ups.',
                'is_active'     => true,
                'priority'      => 5,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Verify the payload was persisted with the translated attributes intact.
        $this->assertDatabaseHas('referral_rewards', [
            'referral_id'        => $referral->getKey(),
            'user_id'            => $referrer->getKey(),
            'type'               => 'discount',
            'amount'             => 10.0,
            'currency_code'      => 'EUR',
            'status'             => 'pending',
            'is_active'          => true,
            'priority'           => 5,
            'title->lt'          => 'Launch Discount',
            'description->lt'    => 'Reward issued for new referral sign ups.',
        ]);
    }

    public function test_edit_page_updates_reward_attributes(): void
    {
        // Seed a reward record that can be modified through the edit form.
        $reward = ReferralReward::factory()->create([
            'title'       => ['lt' => 'Original Title'],
            'description' => ['lt' => 'Original Description'],
            'amount'      => 5.0,
            'type'        => 'credit',
        ]);

        Livewire::test(EditReferralReward::class, ['record' => $reward->getRouteKey()])
            ->fillForm([
                'title'       => 'Updated Title',
                'description' => 'Updated Description',
                'amount'      => 25.5,
                'type'        => 'discount',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Refresh the model to confirm the persisted state matches the submitted payload.
        $reward->refresh();

        self::assertSame('Updated Title', $reward->getTranslation('title', 'lt'));
        self::assertSame('Updated Description', $reward->getTranslation('description', 'lt'));
        self::assertSame(25.5, $reward->amount);
        self::assertSame('discount', $reward->type);
    }

    public function test_view_page_shows_core_details(): void
    {
        // Prepare a reward tied to additional relationships so the infolist renders related data.
        $user = User::factory()->create(['name' => 'Referral Owner']);
        $referral = Referral::factory()->create(['referrer_id' => $user->getKey(), 'referral_code' => 'CODE-123']);
        $order = Order::factory()->create();

        $reward = ReferralReward::factory()->create([
            'referral_id' => $referral->getKey(),
            'user_id'     => $user->getKey(),
            'order_id'    => $order->getKey(),
            'title'       => ['lt' => 'Referral Bonus'],
            'description' => ['lt' => 'Granted for the first purchase.'],
        ]);

        Livewire::test(ViewReferralReward::class, ['record' => $reward->getRouteKey()])
            ->assertSee('Referral Bonus')
            ->assertSee('Granted for the first purchase.')
            ->assertSee('Referral Owner')
            ->assertSee('CODE-123')
            ->assertSee((string) $order->getKey());
    }

    public function test_delete_action_soft_deletes_reward(): void
    {
        // Seed a reward to exercise the table delete action workflow.
        $reward = ReferralReward::factory()->create();

        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->callTableAction('delete', $reward)
            ->assertHasNoTableActionErrors();

        $this->assertSoftDeleted('referral_rewards', ['id' => $reward->getKey()]);
    }

    public function test_filters_limit_results_by_status_type_and_activity(): void
    {
        // Create rewards that differ by status, type, and active flag to drive filter expectations.
        $pending = ReferralReward::factory()->create(['status' => 'pending', 'type' => 'discount', 'is_active' => true]);
        $applied = ReferralReward::factory()->create(['status' => 'applied', 'type' => 'credit', 'is_active' => false]);

        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->filterTable('status', 'pending')
            ->filterTable('type', 'discount')
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$applied]);
    }

    public function test_apply_action_transitions_reward_state(): void
    {
        // Start with a pending reward to confirm the single-record action updates the status and timestamps.
        $reward = ReferralReward::factory()->create(['status' => 'pending']);

        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->callTableAction('apply', $reward)
            ->assertHasNoTableActionErrors();

        $reward->refresh();

        self::assertSame('applied', $reward->status);
        self::assertNotNull($reward->applied_at);
    }

    public function test_expire_action_marks_reward_as_expired(): void
    {
        // Seed a pending reward so the expire action has an eligible record to mutate.
        $reward = ReferralReward::factory()->create(['status' => 'pending']);

        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->callTableAction('expire', $reward)
            ->assertHasNoTableActionErrors();

        $reward->refresh();

        self::assertSame('expired', $reward->status);
    }

    public function test_bulk_actions_update_multiple_rewards(): void
    {
        // Build a collection of pending rewards to exercise the batch apply/expire actions in sequence.
        $rewards = ReferralReward::factory()->count(3)->create(['status' => 'pending']);

        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->callTableBulkAction('apply', $rewards->pluck('id')->all())
            ->assertHasNoTableBulkActionErrors();

        $rewards->each(fn (ReferralReward $reward) => $reward->refresh());
        self::assertTrue($rewards->every(fn (ReferralReward $reward): bool => $reward->status === 'applied'));

        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->callTableBulkAction('expire', $rewards->pluck('id')->all())
            ->assertHasNoTableBulkActionErrors();

        $rewards->each(fn (ReferralReward $reward) => $reward->refresh());
        self::assertTrue($rewards->every(fn (ReferralReward $reward): bool => $reward->status === 'expired'));
    }

    public function test_search_and_sort_controls_are_respected(): void
    {
        // Seed rewards with deterministic titles so search and sort assertions operate on predictable values.
        $alpha = ReferralReward::factory()->create(['title' => ['lt' => 'Alpha Reward']]);
        $beta = ReferralReward::factory()->create(['title' => ['lt' => 'Beta Reward']]);

        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->searchTable('Alpha')
            ->assertCanSeeTableRecords([$alpha])
            ->assertCanNotSeeTableRecords([$beta]);

        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->sortTable('title')
            ->assertCanSeeTableRecordsInOrder([$alpha, $beta]);
    }

    public function test_validation_rules_guard_against_invalid_payloads(): void
    {
        // Attempt to submit an intentionally invalid payload to confirm validation feedback surfaces in the form.
        Livewire::test(CreateReferralReward::class)
            ->fillForm([
                'title'  => '',
                'amount' => 'not-a-number',
            ])
            ->call('create')
            ->assertHasFormErrors(['title', 'amount']);
    }
}
