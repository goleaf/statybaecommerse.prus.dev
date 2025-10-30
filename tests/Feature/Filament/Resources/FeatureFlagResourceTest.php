<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\FeatureFlagResource\Pages\ListFeatureFlags;
use App\Models\FeatureFlag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class FeatureFlagResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the Filament admin panel so route generation and page discovery mirror production behaviour.
        $this->resolveAdminPanel();

        // Stabilise localisation-sensitive output for deterministic assertions inside the Filament tables.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate as an administrator to bypass resource authorisation and expose the Feature Flag pages.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_feature_flag_record(): void
    {
        // Seed a predictable feature flag record so the listing has a known row to assert against.
        $featureFlag = FeatureFlag::factory()->create([
            'name'       => 'Checkout UX Experiment',
            'key'        => 'checkout-ux-experiment',
            'category'   => 'ui',
            'is_active'  => true,
            'is_enabled' => false,
        ]);

        Livewire::test(ListFeatureFlags::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$featureFlag]);
    }

    public function test_category_filter_limits_visible_records(): void
    {
        // Create contrasting feature flags to validate the category filter behaviour.
        $uiFlag = FeatureFlag::factory()->create([
            'name'     => 'Interface Refresh',
            'key'      => 'interface-refresh',
            'category' => 'ui',
        ]);

        $paymentFlag = FeatureFlag::factory()->create([
            'name'     => 'Payment Routing',
            'key'      => 'payment-routing',
            'category' => 'payment',
        ]);

        Livewire::test(ListFeatureFlags::class)
            ->call('loadTable')
            ->filterTable('category', 'payment')
            ->assertCanSeeTableRecords([$paymentFlag])
            ->assertCanNotSeeTableRecords([$uiFlag]);
    }
}
