<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\LegalResource\Pages\ListLegals;
use App\Models\Legal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class LegalResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament resolves the admin panel context before booting any Livewire components.
        $this->resolveAdminPanel();

        // Normalise locales so language-tab driven fields default to English during assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate as an administrator so resource policies permit the upcoming interactions.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_legal_documents(): void
    {
        // Seed a published legal document so the list page has a deterministic record to render.
        $legal = Legal::factory()->create([
            'key'         => 'privacy-policy',
            'type'        => 'privacy_policy',
            'is_enabled'  => true,
            'is_required' => true,
            'published_at' => now()->subDay(),
        ]);

        // Confirm the Livewire table hydrates and surfaces the seeded row in the listing output.
        Livewire::test(ListLegals::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$legal]);
    }

    public function test_type_filter_limits_visible_legal_records(): void
    {
        // Prepare a trio of legal entries that cover published, draft, and disabled permutations.
        Legal::factory()->create([
            'key'         => 'privacy-policy',
            'type'        => 'privacy_policy',
            'is_enabled'  => true,
            'is_required' => true,
            'published_at' => now()->subDay(),
        ]);

        Legal::factory()->create([
            'key'         => 'terms-of-use',
            'type'        => 'terms_of_use',
            'is_enabled'  => true,
            'is_required' => false,
            'published_at' => now()->subDay(),
        ]);

        // Confirm that narrowing to privacy policies hides the terms entry from the table output.
        Livewire::test(ListLegals::class)
            ->call('loadTable')
            ->filterTable('type', 'privacy_policy')
            ->assertSee('privacy-policy')
            ->assertDontSee('terms-of-use');

        // Swap the type filter to reveal the terms document while excluding the privacy policy.
        Livewire::test(ListLegals::class)
            ->call('loadTable')
            ->filterTable('type', 'terms_of_use')
            ->assertSee('terms-of-use')
            ->assertDontSee('privacy-policy');
    }
}
