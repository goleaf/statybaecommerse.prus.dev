<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\PartnerTierResource;
use App\Models\PartnerTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class PartnerTierResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole($adminRole);
        $this->actingAs($this->adminUser);
    }

    public function test_can_render_partner_tier_index_page(): void
    {
        $this->get(PartnerTierResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_list_displays_partner_tiers(): void
    {
        $tiers = [
            PartnerTier::query()->create([
                'name' => 'Gold',
                'code' => 'gold',
                'discount_rate' => 0.1000,
                'commission_rate' => 0.0200,
                'minimum_order_value' => 0,
                'is_enabled' => true,
                'benefits' => ['priority_support' => true],
            ]),
            PartnerTier::query()->create([
                'name' => 'Silver',
                'code' => 'silver',
                'discount_rate' => 0.0500,
                'commission_rate' => 0.0100,
                'minimum_order_value' => 0,
                'is_enabled' => true,
                'benefits' => ['priority_support' => false],
            ]),
        ];

        Livewire::test(PartnerTierResource\Pages\ListPartnerTiers::class)
            ->assertCanSeeTableRecords($tiers);
    }
}


