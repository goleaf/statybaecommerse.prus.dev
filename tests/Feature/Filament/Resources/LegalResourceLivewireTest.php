<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\LegalResource\Pages\ListLegals;
use App\Models\Legal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Smoke tests for the Filament legal documents list page ensuring it hydrates correctly in v4.
 */
final class LegalResourceLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Prime the Filament admin panel so Livewire resolves the configured panel definition.
        $this->resolveAdminPanel();

        // Authenticate as the canonical administrator account expected by the panel.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_surfaces_enabled_legal_entry(): void
    {
        // Seed a published legal document so the listing has deterministic content for assertions.
        $legal = Legal::factory()->enabled()->published()->create([
            'key'  => 'privacy-policy',
            'type' => 'privacy_policy',
        ]);

        // Render the Filament list component and confirm the created document appears in the table output.
        Livewire::test(ListLegals::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$legal]);
    }
}
