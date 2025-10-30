<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\CampaignScheduleResource\Pages\ListCampaignSchedules;
use App\Models\Campaign;
use App\Models\CampaignSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Focused Livewire smoke tests for the campaign schedule list to ensure the v4 page boots correctly.
 */
final class CampaignScheduleResourceLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the Filament admin panel before Livewire mounts run so policies and configuration are available.
        $this->resolveAdminPanel();

        // Provision an administrator so the resource list authorizes successfully during the test run.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_renders_seeded_schedule(): void
    {
        // Create a campaign and attach a deterministic schedule that the table should display.
        $campaign = Campaign::factory()->create([
            'name' => 'Schedule Coverage Campaign',
        ]);
        $schedule = CampaignSchedule::factory()
            ->for($campaign)
            ->create([
                'name'       => 'Coverage Schedule',
                'channel'    => 'email',
                'starts_at'  => now()->subDay(),
                'ends_at'    => now()->addDay(),
                'is_enabled' => true,
            ]);

        // Mount the concrete Filament page and ensure the seeded schedule is visible after hydration.
        Livewire::test(ListCampaignSchedules::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$schedule]);
    }
}
