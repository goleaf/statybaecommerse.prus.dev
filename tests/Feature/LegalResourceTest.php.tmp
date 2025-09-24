<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Legal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestCase;

class LegalResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_legal_documents(): void
    {
        $legal = Legal::factory()->create([
            'key' => 'privacy-policy',
            'type' => 'privacy_policy',
            'is_enabled' => true,
            'is_required' => true,
        ]);

        $this->get('/admin/legals')
            ->assertOk()
            ->assertSee('privacy-policy')
            ->assertSee('Privatumo politika');
    }

    public function test_can_create_legal_document(): void
    {
        $this->get('/admin/legals/create')
            ->assertOk();

        $this->post('/admin/legals', [
            'key' => 'terms-of-use',
            'type' => 'terms_of_use',
            'is_enabled' => true,
            'is_required' => false,
            'sort_order' => 1,
            'published_at' => now(),
        ])->assertRedirect();

        $this->assertDatabaseHas('legals', [
            'key' => 'terms-of-use',
            'type' => 'terms_of_use',
            'is_enabled' => true,
            'is_required' => false,
            'sort_order' => 1,
        ]);
    }

    public function test_can_view_legal_document(): void
    {
        $legal = Legal::factory()->create([
            'key' => 'privacy-policy',
            'type' => 'privacy_policy',
        ]);

        $this->get("/admin/legals/{$legal->id}")
            ->assertOk()
            ->assertSee('privacy-policy');
    }

    public function test_can_edit_legal_document(): void
    {
        $legal = Legal::factory()->create([
            'key' => 'privacy-policy',
            'type' => 'privacy_policy',
            'is_enabled' => true,
        ]);

        $this->get("/admin/legals/{$legal->id}/edit")
            ->assertOk();

        $this->put("/admin/legals/{$legal->id}", [
            'key' => 'privacy-policy',
            'type' => 'privacy_policy',
            'is_enabled' => false,
            'is_required' => true,
            'sort_order' => 2,
        ])->assertRedirect();

        $this->assertDatabaseHas('legals', [
            'id' => $legal->id,
            'is_enabled' => false,
            'is_required' => true,
            'sort_order' => 2,
        ]);
    }

    public function test_can_delete_legal_document(): void
    {
        $legal = Legal::factory()->create();

        $this->delete("/admin/legals/{$legal->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('legals', [
            'id' => $legal->id,
        ]);
    }

    public function test_can_filter_legal_documents_by_type(): void
    {
        Legal::factory()->create([
            'type' => 'privacy_policy',
            'key' => 'privacy-policy',
        ]);

        Legal::factory()->create([
            'type' => 'terms_of_use',
            'key' => 'terms-of-use',
        ]);

        $this->get('/admin/legals?type=privacy_policy')
            ->assertOk()
            ->assertSee('privacy-policy')
            ->assertDontSee('terms-of-use');
    }

    public function test_can_filter_legal_documents_by_enabled_status(): void
    {
        Legal::factory()->create([
            'is_enabled' => true,
            'key' => 'enabled-doc',
        ]);

        Legal::factory()->create([
            'is_enabled' => false,
            'key' => 'disabled-doc',
        ]);

        $this->get('/admin/legals?is_enabled=1')
            ->assertOk()
            ->assertSee('enabled-doc')
            ->assertDontSee('disabled-doc');
    }

    public function test_can_filter_legal_documents_by_required_status(): void
    {
        Legal::factory()->create([
            'is_required' => true,
            'key' => 'required-doc',
        ]);

        Legal::factory()->create([
            'is_required' => false,
            'key' => 'optional-doc',
        ]);

        $this->get('/admin/legals?is_required=1')
            ->assertOk()
            ->assertSee('required-doc')
            ->assertDontSee('optional-doc');
    }

    public function test_can_filter_legal_documents_by_published_status(): void
    {
        Legal::factory()->create([
            'published_at' => now()->subDay(),
            'key' => 'published-doc',
        ]);

        Legal::factory()->create([
            'published_at' => null,
            'key' => 'draft-doc',
        ]);

        $this->get('/admin/legals?published_at=1')
            ->assertOk()
            ->assertSee('published-doc')
            ->assertDontSee('draft-doc');
    }

    public function test_legal_document_types_are_available(): void
    {
        $types = Legal::getTypes();

        $this->assertArrayHasKey('privacy_policy', $types);
        $this->assertArrayHasKey('terms_of_use', $types);
        $this->assertArrayHasKey('refund_policy', $types);
        $this->assertArrayHasKey('shipping_policy', $types);
        $this->assertArrayHasKey('cookie_policy', $types);
        $this->assertArrayHasKey('gdpr_policy', $types);
    }

    public function test_legal_document_required_types(): void
    {
        $requiredTypes = Legal::getRequiredTypes();

        $this->assertContains('privacy_policy', $requiredTypes);
        $this->assertContains('terms_of_use', $requiredTypes);
    }

    public function test_can_get_legal_document_by_key(): void
    {
        $legal = Legal::factory()->create([
            'key' => 'privacy-policy',
            'is_enabled' => true,
            'published_at' => now()->subDay(),
        ]);

        $found = Legal::getByKey('privacy-policy');

        $this->assertNotNull($found);
        $this->assertEquals($legal->id, $found->id);
    }

    public function test_can_get_required_documents(): void
    {
        Legal::factory()->create([
            'is_required' => true,
            'is_enabled' => true,
            'published_at' => now()->subDay(),
            'sort_order' => 1,
        ]);

        Legal::factory()->create([
            'is_required' => false,
            'is_enabled' => true,
            'published_at' => now()->subDay(),
            'sort_order' => 2,
        ]);

        $required = Legal::getRequiredDocuments();

        $this->assertCount(1, $required);
        $this->assertTrue($required->first()->is_required);
    }

    public function test_can_get_documents_by_type(): void
    {
        Legal::factory()->create([
            'type' => 'privacy_policy',
            'is_enabled' => true,
            'published_at' => now()->subDay(),
        ]);

        Legal::factory()->create([
            'type' => 'terms_of_use',
            'is_enabled' => true,
            'published_at' => now()->subDay(),
        ]);

        $privacyDocs = Legal::getByType('privacy_policy');

        $this->assertCount(1, $privacyDocs);
        $this->assertEquals('privacy_policy', $privacyDocs->first()->type);
    }

    public function test_legal_document_status_calculations(): void
    {
        // Disabled document
        $disabled = Legal::factory()->create([
            'is_enabled' => false,
            'published_at' => now()->subDay(),
        ]);

        // Draft document
        $draft = Legal::factory()->create([
            'is_enabled' => true,
            'published_at' => null,
        ]);

        // Published document
        $published = Legal::factory()->create([
            'is_enabled' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->assertEquals('disabled', $disabled->status);
        $this->assertEquals('draft', $draft->status);
        $this->assertEquals('published', $published->status);
    }

    public function test_legal_document_publish_unpublish(): void
    {
        $legal = Legal::factory()->create([
            'published_at' => null,
        ]);

        $this->assertFalse($legal->is_published);

        $legal->publish();
        $this->assertTrue($legal->fresh()->is_published);

        $legal->unpublish();
        $this->assertFalse($legal->fresh()->is_published);
    }

    public function test_legal_document_enable_disable(): void
    {
        $legal = Legal::factory()->create([
            'is_enabled' => false,
        ]);

        $legal->enable();
        $this->assertTrue($legal->fresh()->is_enabled);

        $legal->disable();
        $this->assertFalse($legal->fresh()->is_enabled);
    }

    public function test_legal_document_make_required_optional(): void
    {
        $legal = Legal::factory()->create([
            'is_required' => false,
        ]);

        $legal->makeRequired();
        $this->assertTrue($legal->fresh()->is_required);

        $legal->makeOptional();
        $this->assertFalse($legal->fresh()->is_required);
    }
}
