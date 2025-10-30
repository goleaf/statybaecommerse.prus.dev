<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ReferralCampaignResource\Pages\CreateReferralCampaign;
use App\Filament\Resources\ReferralCampaignResource\Pages\ListReferralCampaigns;
use App\Models\ReferralCampaign;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralCampaignResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament bootstraps with the admin panel configuration before running resource assertions.
        $this->resolveAdminPanel();

        // Load the permissions matrix so the assigned role mirrors production access control lists.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create a deterministic administrator and elevate them to super admin for the referral suite.
        $this->adminUser = User::factory()->create([
            'email'    => 'referral-admin@example.test',
            'is_admin' => true,
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_list_page_displays_active_and_inactive_campaigns(): void
    {
        // Persist both active and inactive referral programmes to validate visibility in the index table.
        $active = ReferralCampaign::factory()->active()->create();
        $inactive = ReferralCampaign::factory()->inactive()->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralCampaigns::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$active, $inactive]);
    }

    public function test_active_filter_hides_inactive_campaigns(): void
    {
        // Create one active and one inactive campaign to exercise the ternary filter branch.
        $active = ReferralCampaign::factory()->active()->create();
        $inactive = ReferralCampaign::factory()->inactive()->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListReferralCampaigns::class)
            ->call('loadTable')
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$inactive]);
    }

    public function test_admin_can_create_referral_campaign_with_translations(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(CreateReferralCampaign::class)
            ->fillForm([
                'name' => [
                    'lt' => 'Rekomendacijų programa',
                    'en' => 'Referral Programme',
                ],
                'description' => [
                    'lt' => 'Skatinkite draugus prisijungti ir gaukite nuolaidą.',
                    'en' => 'Invite friends and earn a discount.',
                ],
                'is_active'              => true,
                'reward_amount'          => '15.00',
                'reward_type'            => 'discount',
                'max_referrals_per_user' => '5',
                'max_total_referrals'    => '250',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_campaigns', [
            'reward_amount' => 15.00,
            'reward_type'   => 'discount',
        ]);
    }
}
