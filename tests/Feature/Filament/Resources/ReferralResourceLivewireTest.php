<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralResource\Pages\ListReferrals;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the Filament referral list page to guard against regressions introduced in v4.
 */
final class ReferralResourceLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize the Filament panel so resource discovery mirrors the production environment.
        $this->resolveAdminPanel();

        // Use an administrator account to pass all resource authorization gates.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_recent_referral(): void
    {
        // Seed a pending referral between two users to ensure the table has meaningful data.
        $referral = Referral::factory()->pending()->create([
            'referral_code' => 'FILAMENT42',
        ]);

        // Boot the Filament page component and assert that the referral row becomes visible after hydration.
        Livewire::test(ListReferrals::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$referral]);
    }
}
