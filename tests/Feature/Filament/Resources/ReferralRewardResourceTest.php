<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralRewardResource;
use App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards;
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

        // Bootstrap the Filament admin panel so Livewire pages share the correct container bindings.
        $this->resolveAdminPanel();

        // Force English translations for predictable assertions when dealing with translatable columns.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Promote a dedicated administrator account for the duration of the feature tests.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_index_page_is_accessible(): void
    {
        // Verify the Filament resource registers its index route without relying on Livewire boot cycles.
        $this
            ->get(ReferralRewardResource::getUrl('index'))
            ->assertOk();
    }

    public function test_list_page_displays_referral_reward_record(): void
    {
        // Create a high-visibility reward so the table has deterministic content to display.
        $reward = ReferralReward::query()->create([
            'referral_id' => null,
            'user_id'     => $this->admin->id,
            'order_id'    => null,
            'type'        => 'credit',
            'title'       => ['en' => 'Launch Bonus', 'lt' => 'Launch Bonus LT'],
            'description' => ['en' => 'Reward granted for beta testers.', 'lt' => 'Reward granted for beta testers.'],
            'amount'      => 42.50,
            'currency_code' => 'EUR',
            'status'      => 'pending',
            'is_active'   => true,
            'priority'    => 1,
            'conditions'  => [],
            'reward_data' => ['category' => 'credit'],
            'metadata'    => [],
        ]);

        // Trigger the Livewire table hydration cycle and ensure the seeded reward appears in the listing.
        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$reward])
            ->assertSee('Launch Bonus')
            ->assertSee('pending');
    }
}
