<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Legal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_can_be_created(): void
    {
        $legal = Legal::factory()->create([
            'type' => 'privacy_policy',
            'title' => 'Privacy Policy',
            'content' => 'This is our privacy policy content',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('legals', [
            'type' => 'privacy_policy',
            'title' => 'Privacy Policy',
            'content' => 'This is our privacy policy content',
            'is_active' => true,
        ]);
    }

    public function test_legal_casts_work_correctly(): void
    {
        $legal = Legal::factory()->create([
            'is_active' => true,
            'created_at' => now(),
        ]);

        $this->assertIsBool($legal->is_active);
        $this->assertInstanceOf(\Carbon\Carbon::class, $legal->created_at);
    }

    public function test_legal_fillable_attributes(): void
    {
        $legal = new Legal();
        $fillable = $legal->getFillable();

        $this->assertContains('type', $fillable);
        $this->assertContains('title', $fillable);
        $this->assertContains('content', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_legal_scope_active(): void
    {
        $activeLegal = Legal::factory()->create(['is_active' => true]);
        $inactiveLegal = Legal::factory()->create(['is_active' => false]);

        $activeLegals = Legal::active()->get();

        $this->assertTrue($activeLegals->contains($activeLegal));
        $this->assertFalse($activeLegals->contains($inactiveLegal));
    }

    public function test_legal_scope_by_type(): void
    {
        $privacyPolicy = Legal::factory()->create(['type' => 'privacy_policy']);
        $termsOfService = Legal::factory()->create(['type' => 'terms_of_service']);

        $privacyPolicies = Legal::byType('privacy_policy')->get();

        $this->assertTrue($privacyPolicies->contains($privacyPolicy));
        $this->assertFalse($privacyPolicies->contains($termsOfService));
    }

    public function test_legal_can_have_versions(): void
    {
        $legal = Legal::factory()->create([
            'version' => '1.0',
            'version_date' => now(),
        ]);

        $this->assertEquals('1.0', $legal->version);
        $this->assertInstanceOf(\Carbon\Carbon::class, $legal->version_date);
    }

    public function test_legal_can_have_effective_date(): void
    {
        $legal = Legal::factory()->create([
            'effective_date' => now()->addDays(30),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $legal->effective_date);
    }

    public function test_legal_can_have_expiry_date(): void
    {
        $legal = Legal::factory()->create([
            'expiry_date' => now()->addYear(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $legal->expiry_date);
    }

    public function test_legal_can_have_metadata(): void
    {
        $legal = Legal::factory()->create([
            'metadata' => [
                'author' => 'Legal Team',
                'reviewed_by' => 'John Doe',
                'approved_by' => 'Jane Smith',
                'tags' => ['privacy', 'gdpr', 'compliance'],
            ],
        ]);

        $this->assertIsArray($legal->metadata);
        $this->assertEquals('Legal Team', $legal->metadata['author']);
        $this->assertEquals('John Doe', $legal->metadata['reviewed_by']);
        $this->assertEquals('Jane Smith', $legal->metadata['approved_by']);
        $this->assertIsArray($legal->metadata['tags']);
    }

    public function test_legal_can_have_translations(): void
    {
        $legal = Legal::factory()->create();

        // Test that legal has translations relationship
        $this->assertTrue(method_exists($legal, 'translations'));
        $this->assertTrue(method_exists($legal, 'trans'));
    }

    public function test_legal_can_have_slug(): void
    {
        $legal = Legal::factory()->create([
            'slug' => 'privacy-policy',
        ]);

        $this->assertEquals('privacy-policy', $legal->slug);
    }

    public function test_legal_can_have_summary(): void
    {
        $legal = Legal::factory()->create([
            'summary' => 'This document outlines our privacy practices and how we collect, use, and protect your personal information.',
        ]);

        $this->assertEquals('This document outlines our privacy practices and how we collect, use, and protect your personal information.', $legal->summary);
    }

    public function test_legal_can_have_required_consent(): void
    {
        $legal = Legal::factory()->create([
            'requires_consent' => true,
        ]);

        $this->assertTrue($legal->requires_consent);
    }

    public function test_legal_can_have_consent_text(): void
    {
        $legal = Legal::factory()->create([
            'consent_text' => 'I agree to the terms and conditions',
        ]);

        $this->assertEquals('I agree to the terms and conditions', $legal->consent_text);
    }

    public function test_legal_can_have_priority(): void
    {
        $legal = Legal::factory()->create([
            'priority' => 'high',
        ]);

        $this->assertEquals('high', $legal->priority);
    }

    public function test_legal_can_have_category(): void
    {
        $legal = Legal::factory()->create([
            'category' => 'privacy',
        ]);

        $this->assertEquals('privacy', $legal->category);
    }

    public function test_legal_can_have_scope(): void
    {
        $legal = Legal::factory()->create([
            'scope' => 'global',
        ]);

        $this->assertEquals('global', $legal->scope);
    }

    public function test_legal_can_have_related_documents(): void
    {
        $legal = Legal::factory()->create([
            'related_documents' => [
                'terms_of_service',
                'cookie_policy',
                'data_processing_agreement',
            ],
        ]);

        $this->assertIsArray($legal->related_documents);
        $this->assertContains('terms_of_service', $legal->related_documents);
        $this->assertContains('cookie_policy', $legal->related_documents);
        $this->assertContains('data_processing_agreement', $legal->related_documents);
    }

    public function test_legal_can_have_contact_information(): void
    {
        $legal = Legal::factory()->create([
            'contact_email' => 'legal@example.com',
            'contact_phone' => '+37012345678',
        ]);

        $this->assertEquals('legal@example.com', $legal->contact_email);
        $this->assertEquals('+37012345678', $legal->contact_phone);
    }

    public function test_legal_can_have_last_updated(): void
    {
        $legal = Legal::factory()->create([
            'last_updated' => now(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $legal->last_updated);
    }

    public function test_legal_can_have_approval_status(): void
    {
        $legal = Legal::factory()->create([
            'approval_status' => 'approved',
        ]);

        $this->assertEquals('approved', $legal->approval_status);
    }
}