<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Legal;
use App\Models\Translations\LegalTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        $legal = Legal::factory()->create();
        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'title' => 'Test Legal Document',
        ]);

        $response = $this->get('/admin/legals');

        $response->assertStatus(200);
        $response->assertSee('Test Legal Document');
    }

    public function test_can_create_legal_document(): void
    {
        $response = $this->get('/admin/legals/create');

        $response->assertStatus(200);
    }

    public function test_can_store_legal_document(): void
    {
        $data = [
            'key' => 'privacy-policy',
            'type' => 'privacy_policy',
            'is_enabled' => true,
            'is_required' => true,
            'sort_order' => 1,
            'published_at' => now()->format('Y-m-d H:i:s'),
            'translations' => [
                'lt' => [
                    'title' => 'Privatumo politika',
                    'slug' => 'privatumo-politika-lt',
                    'content' => '<p>Privatumo politikos turinys...</p>',
                    'seo_title' => 'Privatumo politika - SEO',
                    'seo_description' => 'Privatumo politikos aprašymas',
                ],
                'en' => [
                    'title' => 'Privacy Policy',
                    'slug' => 'privacy-policy-en',
                    'content' => '<p>Privacy policy content...</p>',
                    'seo_title' => 'Privacy Policy - SEO',
                    'seo_description' => 'Privacy policy description',
                ],
            ],
        ];

        $response = $this->post('/admin/legals', $data);

        $response->assertRedirect('/admin/legals');
        
        $this->assertDatabaseHas('legals', [
            'key' => 'privacy-policy',
            'type' => 'privacy_policy',
            'is_enabled' => true,
            'is_required' => true,
        ]);

        $this->assertDatabaseHas('legal_translations', [
            'locale' => 'lt',
            'title' => 'Privatumo politika',
            'slug' => 'privatumo-politika-lt',
        ]);

        $this->assertDatabaseHas('legal_translations', [
            'locale' => 'en',
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy-en',
        ]);
    }

    public function test_can_edit_legal_document(): void
    {
        $legal = Legal::factory()->create();
        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'title' => 'Test Title',
        ]);

        $response = $this->get("/admin/legals/{$legal->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Test Title');
    }

    public function test_can_update_legal_document(): void
    {
        $legal = Legal::factory()->create();
        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'title' => 'Old Title',
        ]);

        $data = [
            'key' => 'updated-key',
            'type' => 'terms_of_use',
            'is_enabled' => false,
            'is_required' => false,
            'sort_order' => 5,
            'translations' => [
                'lt' => [
                    'title' => 'Updated Title',
                    'slug' => 'updated-title-lt',
                    'content' => '<p>Updated content...</p>',
                    'seo_title' => 'Updated SEO Title',
                    'seo_description' => 'Updated SEO description',
                ],
            ],
        ];

        $response = $this->put("/admin/legals/{$legal->id}", $data);

        $response->assertRedirect('/admin/legals');
        
        $this->assertDatabaseHas('legals', [
            'id' => $legal->id,
            'key' => 'updated-key',
            'type' => 'terms_of_use',
            'is_enabled' => false,
            'is_required' => false,
        ]);

        $this->assertDatabaseHas('legal_translations', [
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'title' => 'Updated Title',
        ]);
    }

    public function test_can_delete_legal_document(): void
    {
        $legal = Legal::factory()->create();

        $response = $this->delete("/admin/legals/{$legal->id}");

        $response->assertRedirect('/admin/legals');
        
        $this->assertDatabaseMissing('legals', [
            'id' => $legal->id,
        ]);
    }

    public function test_can_publish_legal_document(): void
    {
        $legal = Legal::factory()->draft()->create();

        $response = $this->post("/admin/legals/{$legal->id}/publish");

        $response->assertRedirect();
        
        $this->assertNotNull($legal->fresh()->published_at);
    }

    public function test_can_unpublish_legal_document(): void
    {
        $legal = Legal::factory()->published()->create();

        $response = $this->post("/admin/legals/{$legal->id}/unpublish");

        $response->assertRedirect();
        
        $this->assertNull($legal->fresh()->published_at);
    }

    public function test_can_enable_legal_document(): void
    {
        $legal = Legal::factory()->disabled()->create();

        $response = $this->post("/admin/legals/{$legal->id}/enable");

        $response->assertRedirect();
        
        $this->assertTrue($legal->fresh()->is_enabled);
    }

    public function test_can_disable_legal_document(): void
    {
        $legal = Legal::factory()->enabled()->create();

        $response = $this->post("/admin/legals/{$legal->id}/disable");

        $response->assertRedirect();
        
        $this->assertFalse($legal->fresh()->is_enabled);
    }

    public function test_can_bulk_publish_legal_documents(): void
    {
        $legal1 = Legal::factory()->draft()->create();
        $legal2 = Legal::factory()->draft()->create();

        $response = $this->post('/admin/legals/bulk-publish', [
            'records' => [$legal1->id, $legal2->id],
        ]);

        $response->assertRedirect();
        
        $this->assertNotNull($legal1->fresh()->published_at);
        $this->assertNotNull($legal2->fresh()->published_at);
    }

    public function test_can_bulk_unpublish_legal_documents(): void
    {
        $legal1 = Legal::factory()->published()->create();
        $legal2 = Legal::factory()->published()->create();

        $response = $this->post('/admin/legals/bulk-unpublish', [
            'records' => [$legal1->id, $legal2->id],
        ]);

        $response->assertRedirect();
        
        $this->assertNull($legal1->fresh()->published_at);
        $this->assertNull($legal2->fresh()->published_at);
    }

    public function test_can_bulk_enable_legal_documents(): void
    {
        $legal1 = Legal::factory()->disabled()->create();
        $legal2 = Legal::factory()->disabled()->create();

        $response = $this->post('/admin/legals/bulk-enable', [
            'records' => [$legal1->id, $legal2->id],
        ]);

        $response->assertRedirect();
        
        $this->assertTrue($legal1->fresh()->is_enabled);
        $this->assertTrue($legal2->fresh()->is_enabled);
    }

    public function test_can_bulk_disable_legal_documents(): void
    {
        $legal1 = Legal::factory()->enabled()->create();
        $legal2 = Legal::factory()->enabled()->create();

        $response = $this->post('/admin/legals/bulk-disable', [
            'records' => [$legal1->id, $legal2->id],
        ]);

        $response->assertRedirect();
        
        $this->assertFalse($legal1->fresh()->is_enabled);
        $this->assertFalse($legal2->fresh()->is_enabled);
    }

    public function test_can_filter_legal_documents_by_type(): void
    {
        $privacyPolicy = Legal::factory()->privacyPolicy()->create();
        $termsOfUse = Legal::factory()->termsOfUse()->create();

        $response = $this->get('/admin/legals?type=privacy_policy');

        $response->assertStatus(200);
        // The response should contain the privacy policy but not terms of use
    }

    public function test_can_filter_legal_documents_by_status(): void
    {
        $publishedLegal = Legal::factory()->enabled()->published()->create();
        $draftLegal = Legal::factory()->enabled()->draft()->create();
        $disabledLegal = Legal::factory()->disabled()->create();

        $response = $this->get('/admin/legals?status=published');

        $response->assertStatus(200);
        // The response should contain only published documents
    }

    public function test_can_search_legal_documents(): void
    {
        $legal = Legal::factory()->create();
        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'title' => 'Privatumo politika',
        ]);

        $response = $this->get('/admin/legals?search=privatumo');

        $response->assertStatus(200);
        $response->assertSee('Privatumo politika');
    }

    public function test_can_view_legal_document(): void
    {
        $legal = Legal::factory()->enabled()->published()->create(['key' => 'privacy-policy']);
        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'title' => 'Privatumo politika',
        ]);

        $response = $this->get("/admin/legals/{$legal->id}/view");

        $response->assertRedirect(route('legal.show', 'privacy-policy'));
    }

    public function test_legal_resource_shows_navigation_badge(): void
    {
        Legal::factory()->count(5)->create();

        $response = $this->get('/admin/legals');

        $response->assertStatus(200);
        // The navigation badge should show the count of legal documents
    }

    public function test_legal_resource_shows_tabs(): void
    {
        $publishedLegal = Legal::factory()->enabled()->published()->create();
        $draftLegal = Legal::factory()->enabled()->draft()->create();
        $disabledLegal = Legal::factory()->disabled()->create();
        $requiredLegal = Legal::factory()->required()->create();

        $response = $this->get('/admin/legals');

        $response->assertStatus(200);
        $response->assertSee('Visi dokumentai');
        $response->assertSee('Paskelbti');
        $response->assertSee('Juodraščiai');
        $response->assertSee('Išjungti');
        $response->assertSee('Privalomi');
    }
}
