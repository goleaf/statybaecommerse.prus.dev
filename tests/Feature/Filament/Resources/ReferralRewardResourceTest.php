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

        // Register the Filament admin panel to align the Livewire component with the correct panel.
        $this->resolveAdminPanel();

        // Standardise translations so table strings remain predictable throughout the assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Prepare an admin guard subject who can access all referral reward pages.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_index_page_lists_rewards(): void
    {
        // Persist a user who will appear as the reward owner inside the grid.
        $recipient = User::factory()->create([
            'name'  => 'Loyal Customer',
            'email' => 'loyal@example.com',
        ]);

        // Create a referral reward with deterministic monetary and status details for the table.
        $reward = ReferralReward::factory()
            ->forUser($recipient)
            ->create([
                'title'       => ['en' => 'Loyalty Credit', 'lt' => 'Lojalumo kreditas'],
                'description' => ['en' => 'Reward for inviting friends', 'lt' => 'Apdovanojimas už draugų pakvietimą'],
                'type'        => 'credit',
                'amount'      => 42.50,
                'currency_code' => 'EUR',
                'status'      => 'pending',
                'is_active'   => true,
            ]);

        $this->actingAs($this->admin);

        // Confirm the index route remains accessible while verifying the Livewire table renders the reward.
        $this->get(ReferralRewardResource::getUrl('index'))->assertOk();

        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$reward])
            ->assertSee('Loyal Customer');
    }
}
