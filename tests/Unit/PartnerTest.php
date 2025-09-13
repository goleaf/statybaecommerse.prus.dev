<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Partner;
use App\Models\PartnerTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_can_be_created(): void
    {
        $partner = Partner::factory()->create([
            'name' => 'Test Partner',
            'code' => 'PARTNER001',
            'type' => 'supplier',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('partners', [
            'name' => 'Test Partner',
            'code' => 'PARTNER001',
            'type' => 'supplier',
            'is_active' => true,
        ]);
    }

    public function test_partner_belongs_to_tier(): void
    {
        $tier = PartnerTier::factory()->create();
        $partner = Partner::factory()->create(['tier_id' => $tier->id]);

        $this->assertInstanceOf(PartnerTier::class, $partner->tier);
        $this->assertEquals($tier->id, $partner->tier->id);
    }

    public function test_partner_casts_work_correctly(): void
    {
        $partner = Partner::factory()->create([
            'is_active' => true,
            'is_verified' => false,
            'sort_order' => 5,
            'created_at' => now(),
        ]);

        $this->assertIsBool($partner->is_active);
        $this->assertIsBool($partner->is_verified);
        $this->assertIsInt($partner->sort_order);
        $this->assertInstanceOf(\Carbon\Carbon::class, $partner->created_at);
    }

    public function test_partner_fillable_attributes(): void
    {
        $partner = new Partner();
        $fillable = $partner->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('code', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_partner_scope_active(): void
    {
        $activePartner = Partner::factory()->create(['is_active' => true]);
        $inactivePartner = Partner::factory()->create(['is_active' => false]);

        $activePartners = Partner::active()->get();

        $this->assertTrue($activePartners->contains($activePartner));
        $this->assertFalse($activePartners->contains($inactivePartner));
    }

    public function test_partner_scope_verified(): void
    {
        $verifiedPartner = Partner::factory()->create(['is_verified' => true]);
        $unverifiedPartner = Partner::factory()->create(['is_verified' => false]);

        $verifiedPartners = Partner::verified()->get();

        $this->assertTrue($verifiedPartners->contains($verifiedPartner));
        $this->assertFalse($verifiedPartners->contains($unverifiedPartner));
    }

    public function test_partner_scope_by_type(): void
    {
        $supplierPartner = Partner::factory()->create(['type' => 'supplier']);
        $distributorPartner = Partner::factory()->create(['type' => 'distributor']);

        $supplierPartners = Partner::byType('supplier')->get();

        $this->assertTrue($supplierPartners->contains($supplierPartner));
        $this->assertFalse($supplierPartners->contains($distributorPartner));
    }

    public function test_partner_scope_ordered(): void
    {
        $partner1 = Partner::factory()->create(['sort_order' => 2]);
        $partner2 = Partner::factory()->create(['sort_order' => 1]);
        $partner3 = Partner::factory()->create(['sort_order' => 3]);

        $orderedPartners = Partner::ordered()->get();

        $this->assertEquals($partner2->id, $orderedPartners->first()->id);
        $this->assertEquals($partner3->id, $orderedPartners->last()->id);
    }

    public function test_partner_can_have_contact_information(): void
    {
        $partner = Partner::factory()->create([
            'contact_person' => 'John Doe',
            'email' => 'john@partner.com',
            'phone' => '+37012345678',
            'website' => 'https://partner.com',
        ]);

        $this->assertEquals('John Doe', $partner->contact_person);
        $this->assertEquals('john@partner.com', $partner->email);
        $this->assertEquals('+37012345678', $partner->phone);
        $this->assertEquals('https://partner.com', $partner->website);
    }

    public function test_partner_can_have_address(): void
    {
        $partner = Partner::factory()->create([
            'address' => '123 Business Street',
            'city' => 'Vilnius',
            'postal_code' => 'LT-01234',
            'country' => 'Lithuania',
        ]);

        $this->assertEquals('123 Business Street', $partner->address);
        $this->assertEquals('Vilnius', $partner->city);
        $this->assertEquals('LT-01234', $partner->postal_code);
        $this->assertEquals('Lithuania', $partner->country);
    }

    public function test_partner_can_have_business_information(): void
    {
        $partner = Partner::factory()->create([
            'company_name' => 'Partner Company Ltd',
            'tax_number' => 'LT123456789',
            'registration_number' => '123456789',
            'business_license' => 'BL123456',
        ]);

        $this->assertEquals('Partner Company Ltd', $partner->company_name);
        $this->assertEquals('LT123456789', $partner->tax_number);
        $this->assertEquals('123456789', $partner->registration_number);
        $this->assertEquals('BL123456', $partner->business_license);
    }

    public function test_partner_can_have_commission_settings(): void
    {
        $partner = Partner::factory()->create([
            'commission_rate' => 5.00,
            'commission_type' => 'percentage',
            'minimum_commission' => 10.00,
            'maximum_commission' => 1000.00,
        ]);

        $this->assertEquals(5.00, $partner->commission_rate);
        $this->assertEquals('percentage', $partner->commission_type);
        $this->assertEquals(10.00, $partner->minimum_commission);
        $this->assertEquals(1000.00, $partner->maximum_commission);
    }

    public function test_partner_can_have_payment_settings(): void
    {
        $partner = Partner::factory()->create([
            'payment_terms' => 'net_30',
            'payment_method' => 'bank_transfer',
            'bank_account' => 'LT123456789012345678',
            'bank_name' => 'Swedbank',
        ]);

        $this->assertEquals('net_30', $partner->payment_terms);
        $this->assertEquals('bank_transfer', $partner->payment_method);
        $this->assertEquals('LT123456789012345678', $partner->bank_account);
        $this->assertEquals('Swedbank', $partner->bank_name);
    }

    public function test_partner_can_have_agreement_details(): void
    {
        $partner = Partner::factory()->create([
            'agreement_start_date' => now(),
            'agreement_end_date' => now()->addYear(),
            'agreement_type' => 'exclusive',
            'agreement_status' => 'active',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $partner->agreement_start_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $partner->agreement_end_date);
        $this->assertEquals('exclusive', $partner->agreement_type);
        $this->assertEquals('active', $partner->agreement_status);
    }

    public function test_partner_can_have_performance_metrics(): void
    {
        $partner = Partner::factory()->create([
            'performance_metrics' => [
                'total_orders' => 150,
                'total_revenue' => 50000.00,
                'average_order_value' => 333.33,
                'customer_satisfaction' => 4.5,
            ],
        ]);

        $this->assertIsArray($partner->performance_metrics);
        $this->assertEquals(150, $partner->performance_metrics['total_orders']);
        $this->assertEquals(50000.00, $partner->performance_metrics['total_revenue']);
        $this->assertEquals(333.33, $partner->performance_metrics['average_order_value']);
        $this->assertEquals(4.5, $partner->performance_metrics['customer_satisfaction']);
    }

    public function test_partner_can_have_metadata(): void
    {
        $partner = Partner::factory()->create([
            'metadata' => [
                'created_by' => 'admin',
                'approval_status' => 'approved',
                'special_notes' => 'Preferred supplier',
                'tags' => ['reliable', 'fast_shipping', 'quality'],
            ],
        ]);

        $this->assertIsArray($partner->metadata);
        $this->assertEquals('admin', $partner->metadata['created_by']);
        $this->assertEquals('approved', $partner->metadata['approval_status']);
        $this->assertEquals('Preferred supplier', $partner->metadata['special_notes']);
        $this->assertIsArray($partner->metadata['tags']);
    }

    public function test_partner_can_have_media(): void
    {
        $partner = Partner::factory()->create();

        // Test that partner implements HasMedia
        $this->assertInstanceOf(\Spatie\MediaLibrary\HasMedia::class, $partner);
        
        // Test that partner can handle media
        $this->assertTrue(method_exists($partner, 'registerMediaCollections'));
        $this->assertTrue(method_exists($partner, 'registerMediaConversions'));
        $this->assertTrue(method_exists($partner, 'media'));
    }

    public function test_partner_can_have_translations(): void
    {
        $partner = Partner::factory()->create();

        // Test that partner has translations relationship
        $this->assertTrue(method_exists($partner, 'translations'));
        $this->assertTrue(method_exists($partner, 'trans'));
    }

    public function test_partner_can_have_rating(): void
    {
        $partner = Partner::factory()->create([
            'rating' => 4.5,
            'rating_count' => 25,
        ]);

        $this->assertEquals(4.5, $partner->rating);
        $this->assertEquals(25, $partner->rating_count);
    }

    public function test_partner_can_have_status(): void
    {
        $partner = Partner::factory()->create([
            'status' => 'active',
        ]);

        $this->assertEquals('active', $partner->status);
    }

    public function test_partner_can_have_notes(): void
    {
        $partner = Partner::factory()->create([
            'notes' => 'Important partner with special requirements',
        ]);

        $this->assertEquals('Important partner with special requirements', $partner->notes);
    }
}
