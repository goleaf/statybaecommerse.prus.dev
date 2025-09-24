<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Models\Partner;
use App\Models\PartnerTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestCase;

class PartnerResourceTest extends TestCase
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

        // Create test partner tier
        $this->partnerTier = PartnerTier::factory()->create();
    }

    public function test_can_list_partners(): void
    {
        $partner = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\ListPartners::class)
            ->assertCanSeeTableRecords([$partner]);
    }

    public function test_can_create_partner(): void
    {
        $this->actingAs($this->user);

        $partnerData = [
            'name' => 'Test Partner',
            'code' => 'TEST001',
            'tier_id' => $this->partnerTier->id,
            'is_enabled' => true,
            'contact_email' => 'partner@test.com',
            'contact_phone' => '+37060000000',
            'discount_rate' => 10.5,
            'commission_rate' => 5.0,
        ];

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\CreatePartner::class)
            ->fillForm($partnerData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('partners', [
            'name' => 'Test Partner',
            'code' => 'TEST001',
            'tier_id' => $this->partnerTier->id,
            'is_enabled' => true,
            'contact_email' => 'partner@test.com',
            'contact_phone' => '+37060000000',
            'discount_rate' => 10.5,
            'commission_rate' => 5.0,
        ]);
    }

    public function test_can_view_partner(): void
    {
        $partner = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\ViewPartner::class, ['record' => $partner->id])
            ->assertCanSeeRecord($partner);
    }

    public function test_can_edit_partner(): void
    {
        $partner = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'name' => 'Original Name',
            'discount_rate' => 5.0,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\EditPartner::class, ['record' => $partner->id])
            ->fillForm([
                'name' => 'Updated Name',
                'discount_rate' => 15.0,
                'commission_rate' => 7.5,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'name' => 'Updated Name',
            'discount_rate' => 15.0,
            'commission_rate' => 7.5,
        ]);
    }

    public function test_can_delete_partner(): void
    {
        $partner = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\ListPartners::class)
            ->callTableAction('delete', $partner)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('partners', ['id' => $partner->id]);
    }

    public function test_can_filter_partners_by_tier(): void
    {
        $tier1 = PartnerTier::factory()->create(['name' => 'Gold']);
        $tier2 = PartnerTier::factory()->create(['name' => 'Silver']);

        $partner1 = Partner::factory()->create(['tier_id' => $tier1->id]);
        $partner2 = Partner::factory()->create(['tier_id' => $tier2->id]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\ListPartners::class)
            ->filterTable('tier_id', $tier1->id)
            ->assertCanSeeTableRecords([$partner1])
            ->assertCanNotSeeTableRecords([$partner2]);
    }

    public function test_can_filter_partners_by_enabled_status(): void
    {
        $enabledPartner = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'is_enabled' => true,
        ]);

        $disabledPartner = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'is_enabled' => false,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\ListPartners::class)
            ->filterTable('is_enabled', true)
            ->assertCanSeeTableRecords([$enabledPartner])
            ->assertCanNotSeeTableRecords([$disabledPartner]);
    }

    public function test_can_search_partners_by_name(): void
    {
        $partner1 = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'name' => 'ABC Company',
        ]);

        $partner2 = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'name' => 'XYZ Corporation',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\ListPartners::class)
            ->searchTable('ABC Company')
            ->assertCanSeeTableRecords([$partner1])
            ->assertCanNotSeeTableRecords([$partner2]);
    }

    public function test_can_search_partners_by_code(): void
    {
        $partner1 = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'code' => 'PART001',
        ]);

        $partner2 = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'code' => 'PART002',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\ListPartners::class)
            ->searchTable('PART001')
            ->assertCanSeeTableRecords([$partner1])
            ->assertCanNotSeeTableRecords([$partner2]);
    }

    public function test_can_bulk_delete_partners(): void
    {
        $partners = Partner::factory()->count(3)->create([
            'tier_id' => $this->partnerTier->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\ListPartners::class)
            ->callTableBulkAction('delete', $partners)
            ->assertHasNoTableBulkActionErrors();

        foreach ($partners as $partner) {
            $this->assertDatabaseMissing('partners', ['id' => $partner->id]);
        }
    }

    public function test_can_sort_partners_by_name(): void
    {
        $partner1 = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'name' => 'Zebra Company',
        ]);

        $partner2 = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'name' => 'Alpha Corporation',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\ListPartners::class)
            ->sortTable('name', 'asc')
            ->assertCanSeeTableRecords([$partner2, $partner1]);
    }

    public function test_can_sort_partners_by_discount_rate(): void
    {
        $partner1 = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'discount_rate' => 15.0,
        ]);

        $partner2 = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'discount_rate' => 5.0,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\ListPartners::class)
            ->sortTable('discount_rate', 'asc')
            ->assertCanSeeTableRecords([$partner2, $partner1]);
    }

    public function test_can_sort_partners_by_commission_rate(): void
    {
        $partner1 = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'commission_rate' => 10.0,
        ]);

        $partner2 = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'commission_rate' => 3.0,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\ListPartners::class)
            ->sortTable('commission_rate', 'asc')
            ->assertCanSeeTableRecords([$partner2, $partner1]);
    }

    public function test_can_toggle_partner_columns(): void
    {
        $partner = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\ListPartners::class)
            ->assertCanSeeTableRecords([$partner])
            ->assertTableColumnExists('name')
            ->assertTableColumnExists('code')
            ->assertTableColumnExists('tier.name')
            ->assertTableColumnExists('is_enabled')
            ->assertTableColumnExists('discount_rate')
            ->assertTableColumnExists('commission_rate')
            ->assertTableColumnExists('created_at');
    }

    public function test_can_validate_partner_code_uniqueness(): void
    {
        $existingPartner = Partner::factory()->create([
            'tier_id' => $this->partnerTier->id,
            'code' => 'EXISTING001',
        ]);

        $this->actingAs($this->user);

        $partnerData = [
            'name' => 'Test Partner',
            'code' => 'EXISTING001', // Same code as existing partner
            'tier_id' => $this->partnerTier->id,
            'is_enabled' => true,
        ];

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\CreatePartner::class)
            ->fillForm($partnerData)
            ->call('create')
            ->assertHasFormErrors(['code']);
    }

    public function test_can_validate_partner_email_format(): void
    {
        $this->actingAs($this->user);

        $partnerData = [
            'name' => 'Test Partner',
            'code' => 'TEST001',
            'tier_id' => $this->partnerTier->id,
            'is_enabled' => true,
            'contact_email' => 'invalid-email', // Invalid email format
        ];

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\CreatePartner::class)
            ->fillForm($partnerData)
            ->call('create')
            ->assertHasFormErrors(['contact_email']);
    }

    public function test_can_validate_partner_discount_rate_range(): void
    {
        $this->actingAs($this->user);

        $partnerData = [
            'name' => 'Test Partner',
            'code' => 'TEST001',
            'tier_id' => $this->partnerTier->id,
            'is_enabled' => true,
            'discount_rate' => 150.0, // Invalid: exceeds 100%
        ];

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\CreatePartner::class)
            ->fillForm($partnerData)
            ->call('create')
            ->assertHasFormErrors(['discount_rate']);
    }

    public function test_can_validate_partner_commission_rate_range(): void
    {
        $this->actingAs($this->user);

        $partnerData = [
            'name' => 'Test Partner',
            'code' => 'TEST001',
            'tier_id' => $this->partnerTier->id,
            'is_enabled' => true,
            'commission_rate' => 150.0, // Invalid: exceeds 100%
        ];

        Livewire::test(\App\Filament\Resources\PartnerResource\Pages\CreatePartner::class)
            ->fillForm($partnerData)
            ->call('create')
            ->assertHasFormErrors(['commission_rate']);
    }
}
