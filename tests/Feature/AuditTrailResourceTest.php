<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\AuditTrailResource\Pages\ListAuditTrails;
use App\Filament\Resources\AuditTrailResource\Pages\ViewAuditTrail;
use App\Models\AuditTrail;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

final class AuditTrailResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialise the Filament admin panel so Livewire components resolve navigation and policies correctly.
        $this->resolveAdminPanel();

        // Seed the full permissions matrix to mirror production role capabilities inside the test harness.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Promote a deterministic administrator account and attach the super admin role for unrestricted access.
        $this->adminUser = User::factory()->create([
            'email'    => 'audit-trails-admin@example.test',
            'is_admin' => true,
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_list_page_displays_recent_audit_events(): void
    {
        // Craft two audit trail rows so the index table has meaningful payloads to render.
        $entries = AuditTrail::factory()->count(2)->create([
            'event' => 'price.updated',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListAuditTrails::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords($entries);
    }

    public function test_event_filter_limits_results_to_selected_action(): void
    {
        // Generate a mixed dataset so filtering to price updates hides inventory changes.
        $priceAudit = AuditTrail::factory()->create([
            'event' => 'price.updated',
            'reason' => 'Pricing adjustment verification.',
        ]);
        $inventoryAudit = AuditTrail::factory()->create([
            'event' => 'inventory.updated',
            'reason' => 'Inventory sync verification.',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListAuditTrails::class)
            ->call('loadTable')
            ->filterTable('event', 'price.updated')
            ->assertCanSeeTableRecords([$priceAudit])
            ->assertCanNotSeeTableRecords([$inventoryAudit]);
    }

    public function test_view_page_surfaces_audit_metadata(): void
    {
        // Persist a detailed audit entry so the infolist mirrors the formatted diff output.
        $actor = User::factory()->create(['name' => 'Diff Reviewer']);
        $subject = User::factory()->create(['name' => 'Audited Customer']);

        $audit = AuditTrail::query()->create([
            'auditable_type' => $subject->getMorphClass(),
            'auditable_id'   => $subject->getKey(),
            'event'          => 'user.updated',
            'actor_type'     => $actor->getMorphClass(),
            'actor_id'       => $actor->getKey(),
            'reason'         => 'Profile synchronisation.',
            'request_id'     => (string) Str::uuid(),
            'diff'           => [
                'email' => [
                    'previous' => 'old@example.test',
                    'current'  => 'new@example.test',
                ],
            ],
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ViewAuditTrail::class, ['record' => $audit->getKey()])
            ->assertSee('Profile synchronisation.')
            ->assertSee('old@example.test')
            ->assertSee('new@example.test');
    }
}
