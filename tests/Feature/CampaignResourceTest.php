<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CampaignResource\Pages\CreateCampaign;
use App\Filament\Resources\CampaignResource\Pages\ListCampaigns;
use App\Models\Campaign;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CampaignResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Activate the Filament admin panel so resource discovery mirrors production boot order.
        $this->resolveAdminPanel();

        // Load the permissions matrix to guarantee the administrative role exposes all campaign abilities.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create a reusable administrator with super admin powers for all Filament interactions.
        $this->adminUser = User::factory()->create([
            'email'    => 'campaign-admin@example.test',
            'is_admin' => true,
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_list_page_shows_active_and_draft_campaigns(): void
    {
        // Provide both an active and draft campaign so the table proves it bypasses status scopes.
        $active = Campaign::factory()->create(['status' => 'active']);
        $draft = Campaign::factory()->create(['status' => 'draft']);

        Livewire::actingAs($this->adminUser)
            ->test(ListCampaigns::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$active, $draft]);
    }

    public function test_status_filter_can_focus_on_active_campaigns(): void
    {
        // Prepare contrasting campaign statuses to exercise the select filter logic.
        $active = Campaign::factory()->create(['status' => 'active']);
        $paused = Campaign::factory()->create(['status' => 'paused']);

        Livewire::actingAs($this->adminUser)
            ->test(ListCampaigns::class)
            ->call('loadTable')
            ->filterTable('status', 'active')
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$paused]);
    }

    public function test_admin_can_create_campaign_with_minimal_details(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(CreateCampaign::class)
            ->fillForm([
                'name'        => 'Launch Campaign',
                'slug'        => 'launch-campaign',
                'status'      => 'active',
                'is_active'   => true,
                'description' => 'Teaser copy for the launch initiative.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('campaigns', [
            'slug'   => 'launch-campaign',
            'status' => 'active',
        ]);
    }
}
