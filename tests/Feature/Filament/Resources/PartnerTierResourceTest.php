<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Models\PartnerTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PartnerTierResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user with admin role
        $this->user = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_partner_tiers(): void
    {
        $partnerTier = PartnerTier::factory()->create();

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\ListPartnerTiers::class)
            ->assertCanSeeTableRecords([$partnerTier]);
    }

    public function test_can_create_partner_tier(): void
    {
        $this->actingAs($this->user);

        $partnerTierData = [
            'name' => 'Gold Tier',
            'code' => 'GOLD',
            'is_enabled' => true,
            'discount_rate' => 15.0,
            'commission_rate' => 8.0,
            'minimum_order_value' => 1000.0,
            'benefits' => [
                ['benefit' => 'Priority Support'],
                ['benefit' => 'Extended Warranty'],
                ['benefit' => 'Free Shipping'],
            ],
        ];

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\CreatePartnerTier::class)
            ->fillForm($partnerTierData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('partner_tiers', [
            'name' => 'Gold Tier',
            'code' => 'GOLD',
            'is_enabled' => true,
            'discount_rate' => 15.0,
            'commission_rate' => 8.0,
            'minimum_order_value' => 1000.0,
        ]);
    }

    public function test_can_view_partner_tier(): void
    {
        $partnerTier = PartnerTier::factory()->create();

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\ViewPartnerTier::class, ['record' => $partnerTier->id])
            ->assertCanSeeRecord($partnerTier);
    }

    public function test_can_edit_partner_tier(): void
    {
        $partnerTier = PartnerTier::factory()->create([
            'name' => 'Original Tier',
            'discount_rate' => 5.0,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\EditPartnerTier::class, ['record' => $partnerTier->id])
            ->fillForm([
                'name' => 'Updated Tier',
                'discount_rate' => 20.0,
                'commission_rate' => 12.0,
                'minimum_order_value' => 2000.0,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('partner_tiers', [
            'id' => $partnerTier->id,
            'name' => 'Updated Tier',
            'discount_rate' => 20.0,
            'commission_rate' => 12.0,
            'minimum_order_value' => 2000.0,
        ]);
    }

    public function test_can_delete_partner_tier(): void
    {
        $partnerTier = PartnerTier::factory()->create();

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\ListPartnerTiers::class)
            ->callTableAction('delete', $partnerTier)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('partner_tiers', ['id' => $partnerTier->id]);
    }

    public function test_can_filter_partner_tiers_by_enabled_status(): void
    {
        $enabledTier = PartnerTier::factory()->create(['is_enabled' => true]);
        $disabledTier = PartnerTier::factory()->create(['is_enabled' => false]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\ListPartnerTiers::class)
            ->filterTable('is_enabled', true)
            ->assertCanSeeTableRecords([$enabledTier])
            ->assertCanNotSeeTableRecords([$disabledTier]);
    }

    public function test_can_search_partner_tiers_by_name(): void
    {
        $tier1 = PartnerTier::factory()->create(['name' => 'Gold Tier']);
        $tier2 = PartnerTier::factory()->create(['name' => 'Silver Tier']);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\ListPartnerTiers::class)
            ->searchTable('Gold Tier')
            ->assertCanSeeTableRecords([$tier1])
            ->assertCanNotSeeTableRecords([$tier2]);
    }

    public function test_can_search_partner_tiers_by_code(): void
    {
        $tier1 = PartnerTier::factory()->create(['code' => 'GOLD']);
        $tier2 = PartnerTier::factory()->create(['code' => 'SILVER']);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\ListPartnerTiers::class)
            ->searchTable('GOLD')
            ->assertCanSeeTableRecords([$tier1])
            ->assertCanNotSeeTableRecords([$tier2]);
    }

    public function test_can_bulk_delete_partner_tiers(): void
    {
        $partnerTiers = PartnerTier::factory()->count(3)->create();

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\ListPartnerTiers::class)
            ->callTableBulkAction('delete', $partnerTiers)
            ->assertHasNoTableBulkActionErrors();

        foreach ($partnerTiers as $partnerTier) {
            $this->assertDatabaseMissing('partner_tiers', ['id' => $partnerTier->id]);
        }
    }

    public function test_can_sort_partner_tiers_by_name(): void
    {
        $tier1 = PartnerTier::factory()->create(['name' => 'Zebra Tier']);
        $tier2 = PartnerTier::factory()->create(['name' => 'Alpha Tier']);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\ListPartnerTiers::class)
            ->sortTable('name', 'asc')
            ->assertCanSeeTableRecords([$tier2, $tier1]);
    }

    public function test_can_sort_partner_tiers_by_discount_rate(): void
    {
        $tier1 = PartnerTier::factory()->create(['discount_rate' => 20.0]);
        $tier2 = PartnerTier::factory()->create(['discount_rate' => 5.0]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\ListPartnerTiers::class)
            ->sortTable('discount_rate', 'asc')
            ->assertCanSeeTableRecords([$tier2, $tier1]);
    }

    public function test_can_sort_partner_tiers_by_commission_rate(): void
    {
        $tier1 = PartnerTier::factory()->create(['commission_rate' => 15.0]);
        $tier2 = PartnerTier::factory()->create(['commission_rate' => 3.0]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\ListPartnerTiers::class)
            ->sortTable('commission_rate', 'asc')
            ->assertCanSeeTableRecords([$tier2, $tier1]);
    }

    public function test_can_toggle_partner_tier_columns(): void
    {
        $partnerTier = PartnerTier::factory()->create();

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\ListPartnerTiers::class)
            ->assertCanSeeTableRecords([$partnerTier])
            ->assertTableColumnExists('name')
            ->assertTableColumnExists('code')
            ->assertTableColumnExists('is_enabled')
            ->assertTableColumnExists('discount_rate')
            ->assertTableColumnExists('commission_rate')
            ->assertTableColumnExists('created_at');
    }

    public function test_can_validate_partner_tier_code_uniqueness(): void
    {
        $existingTier = PartnerTier::factory()->create(['code' => 'EXISTING']);

        $this->actingAs($this->user);

        $tierData = [
            'name' => 'Test Tier',
            'code' => 'EXISTING',  // Same code as existing tier
            'is_enabled' => true,
        ];

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\CreatePartnerTier::class)
            ->fillForm($tierData)
            ->call('create')
            ->assertHasFormErrors(['code']);
    }

    public function test_can_validate_partner_tier_discount_rate_range(): void
    {
        $this->actingAs($this->user);

        $tierData = [
            'name' => 'Test Tier',
            'code' => 'TEST',
            'is_enabled' => true,
            'discount_rate' => 150.0,  // Invalid: exceeds 100%
        ];

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\CreatePartnerTier::class)
            ->fillForm($tierData)
            ->call('create')
            ->assertHasFormErrors(['discount_rate']);
    }

    public function test_can_validate_partner_tier_commission_rate_range(): void
    {
        $this->actingAs($this->user);

        $tierData = [
            'name' => 'Test Tier',
            'code' => 'TEST',
            'is_enabled' => true,
            'commission_rate' => 150.0,  // Invalid: exceeds 100%
        ];

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\CreatePartnerTier::class)
            ->fillForm($tierData)
            ->call('create')
            ->assertHasFormErrors(['commission_rate']);
    }

    public function test_can_validate_partner_tier_minimum_order_value(): void
    {
        $this->actingAs($this->user);

        $tierData = [
            'name' => 'Test Tier',
            'code' => 'TEST',
            'is_enabled' => true,
            'minimum_order_value' => -100.0,  // Invalid: negative value
        ];

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\CreatePartnerTier::class)
            ->fillForm($tierData)
            ->call('create')
            ->assertHasFormErrors(['minimum_order_value']);
    }

    public function test_can_create_partner_tier_with_benefits(): void
    {
        $this->actingAs($this->user);

        $tierData = [
            'name' => 'Premium Tier',
            'code' => 'PREMIUM',
            'is_enabled' => true,
            'discount_rate' => 25.0,
            'commission_rate' => 10.0,
            'minimum_order_value' => 5000.0,
            'benefits' => [
                ['benefit' => '24/7 Support'],
                ['benefit' => 'Free Shipping Worldwide'],
                ['benefit' => 'Extended Warranty'],
                ['benefit' => 'Priority Processing'],
            ],
        ];

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\CreatePartnerTier::class)
            ->fillForm($tierData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('partner_tiers', [
            'name' => 'Premium Tier',
            'code' => 'PREMIUM',
            'discount_rate' => 25.0,
            'commission_rate' => 10.0,
            'minimum_order_value' => 5000.0,
        ]);
    }

    public function test_can_edit_partner_tier_benefits(): void
    {
        $partnerTier = PartnerTier::factory()->create([
            'benefits' => [
                ['benefit' => 'Basic Support'],
            ],
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\EditPartnerTier::class, ['record' => $partnerTier->id])
            ->fillForm([
                'benefits' => [
                    ['benefit' => 'Premium Support'],
                    ['benefit' => 'Free Shipping'],
                    ['benefit' => 'Extended Warranty'],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $updatedTier = PartnerTier::find($partnerTier->id);
        $this->assertCount(3, $updatedTier->benefits);
        $this->assertEquals('Premium Support', $updatedTier->benefits[0]['benefit']);
        $this->assertEquals('Free Shipping', $updatedTier->benefits[1]['benefit']);
        $this->assertEquals('Extended Warranty', $updatedTier->benefits[2]['benefit']);
    }

    public function test_can_toggle_partner_tier_enabled_status(): void
    {
        $partnerTier = PartnerTier::factory()->create(['is_enabled' => true]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\EditPartnerTier::class, ['record' => $partnerTier->id])
            ->fillForm(['is_enabled' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('partner_tiers', [
            'id' => $partnerTier->id,
            'is_enabled' => false,
        ]);
    }
}
