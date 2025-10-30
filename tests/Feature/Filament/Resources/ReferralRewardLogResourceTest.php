<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralRewardLogResource\Pages\ListReferralRewardLogs;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralRewardLogResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the Filament admin panel so Livewire components load inside the admin context.
        $this->resolveAdminPanel();

        // Force English so translated title columns flatten to deterministic strings.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Create an administrator used to satisfy Filament authorization gates.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_list_page_displays_reward_logs(): void
    {
        // Persist a referral participant whose profile populates the log table's user column.
        $beneficiary = User::factory()->create([
            'name'  => 'Reward Recipient',
            'email' => 'recipient@example.com',
        ]);

        // Create a deterministic reward so badge, amount, and relationship columns stay predictable.
        $reward = ReferralReward::factory()
            ->forUser($beneficiary)
            ->create([
                'title'       => ['en' => 'Welcome Bonus', 'lt' => 'Sveikinimo bonusas'],
                'description' => ['en' => 'Signup incentive', 'lt' => 'Registracijos paskata'],
                'type'        => 'credit',
                'amount'      => 25.00,
                'status'      => 'applied',
                'is_active'   => true,
            ]);

        // Record a lifecycle log entry with fixed metadata so assertions can target concrete strings.
        $log = ReferralRewardLog::factory()
            ->for($reward)
            ->for($beneficiary)
            ->earned()
            ->create([
                'ip_address' => '198.51.100.5',
                'user_agent' => 'RewardBot/1.0',
            ]);

        $this->actingAs($this->admin);

        // Ensure the reward log listing renders the seeded entry with the associated user context.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$log])
            ->assertSee('Reward Recipient')
            ->assertSee('198.51.100.5');
    }
}
