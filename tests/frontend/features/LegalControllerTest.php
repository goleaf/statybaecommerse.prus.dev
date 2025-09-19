<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Legal;
use App\Models\Translations\LegalTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_index_page_displays_legal_documents(): void
    {
        $legal = Legal::factory()->enabled()->published()->create();
        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'title' => 'Test Legal Document',
        ]);

        $response = $this->get(route('legal.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Legal Document');
    }

    public function test_legal_show_page_displays_specific_document(): void
    {
        $legal = Legal::factory()->enabled()->published()->create(['key' => 'privacy-policy']);
        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'title' => 'Privatumo politika',
            'content' => '<p>Privatumo politikos turinys...</p>',
        ]);

        $response = $this->get(route('legal.show', 'privacy-policy'));

        $response->assertStatus(200);
        $response->assertSee('Privatumo politika');
        $response->assertSee('Privatumo politikos turinys...');
    }

    public function test_legal_show_page_returns_404_for_non_existent_document(): void
    {
        $response = $this->get(route('legal.show', 'non-existent'));

        $response->assertStatus(404);
    }

    public function test_legal_show_page_returns_404_for_disabled_document(): void
    {
        $legal = Legal::factory()->disabled()->create(['key' => 'disabled-doc']);

        $response = $this->get(route('legal.show', 'disabled-doc'));

        $response->assertStatus(404);
    }

    public function test_legal_show_page_returns_404_for_unpublished_document(): void
    {
        $legal = Legal::factory()->draft()->create(['key' => 'draft-doc']);

        $response = $this->get(route('legal.show', 'draft-doc'));

        $response->assertStatus(404);
    }

    public function test_legal_type_page_displays_documents_by_type(): void
    {
        $privacyPolicy = Legal::factory()->privacyPolicy()->enabled()->published()->create();
        $termsOfUse = Legal::factory()->termsOfUse()->enabled()->published()->create();

        LegalTranslation::factory()->create([
            'legal_id' => $privacyPolicy->id,
            'locale' => 'lt',
            'title' => 'Privatumo politika',
        ]);

        LegalTranslation::factory()->create([
            'legal_id' => $termsOfUse->id,
            'locale' => 'lt',
            'title' => 'Naudojimosi sąlygos',
        ]);

        $response = $this->get(route('legal.type', 'privacy_policy'));

        $response->assertStatus(200);
        $response->assertSee('Privatumo politika');
        $response->assertDontSee('Naudojimosi sąlygos');
    }

    public function test_legal_search_page_displays_search_results(): void
    {
        $legal = Legal::factory()->enabled()->published()->create();
        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'title' => 'Privatumo politika',
            'content' => 'Privatumo politikos turinys su raktažodžiais',
        ]);

        $response = $this->get(route('legal.search', ['q' => 'privatumo']));

        $response->assertStatus(200);
        $response->assertSee('Privatumo politika');
    }

    public function test_legal_search_page_filters_by_type(): void
    {
        $privacyPolicy = Legal::factory()->privacyPolicy()->enabled()->published()->create();
        $termsOfUse = Legal::factory()->termsOfUse()->enabled()->published()->create();

        LegalTranslation::factory()->create([
            'legal_id' => $privacyPolicy->id,
            'locale' => 'lt',
            'title' => 'Privatumo politika',
        ]);

        LegalTranslation::factory()->create([
            'legal_id' => $termsOfUse->id,
            'locale' => 'lt',
            'title' => 'Naudojimosi sąlygos',
        ]);

        $response = $this->get(route('legal.search', ['type' => 'privacy_policy']));

        $response->assertStatus(200);
        $response->assertSee('Privatumo politika');
        $response->assertDontSee('Naudojimosi sąlygos');
    }

    public function test_legal_sitemap_returns_xml(): void
    {
        $legal = Legal::factory()->enabled()->published()->create();
        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'slug' => 'privacy-policy-lt',
        ]);

        $response = $this->get(route('legal.sitemap'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('privacy-policy-lt');
    }

    public function test_legal_rss_returns_xml(): void
    {
        $legal = Legal::factory()->enabled()->published()->create();
        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'title' => 'Privatumo politika',
        ]);

        $response = $this->get(route('legal.rss'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/rss+xml');
        $response->assertSee('Privatumo politika');
    }

    public function test_legal_download_redirects_to_show_page(): void
    {
        $legal = Legal::factory()->enabled()->published()->create(['key' => 'privacy-policy']);

        $response = $this->get(route('legal.download', ['key' => 'privacy-policy', 'format' => 'pdf']));

        $response->assertRedirect(route('legal.show', 'privacy-policy'));
    }

    public function test_legal_index_page_shows_contact_section(): void
    {
        $response = $this->get(route('legal.index'));

        $response->assertStatus(200);
        $response->assertSee('Turite klausimų apie teisinius dokumentus?');
    }

    public function test_legal_show_page_shows_related_documents(): void
    {
        $privacyPolicy = Legal::factory()->privacyPolicy()->enabled()->published()->create();
        $termsOfUse = Legal::factory()->termsOfUse()->enabled()->published()->create();

        LegalTranslation::factory()->create([
            'legal_id' => $privacyPolicy->id,
            'locale' => 'lt',
            'title' => 'Privatumo politika',
        ]);

        LegalTranslation::factory()->create([
            'legal_id' => $termsOfUse->id,
            'locale' => 'lt',
            'title' => 'Naudojimosi sąlygos',
        ]);

        $response = $this->get(route('legal.show', $privacyPolicy->key));

        $response->assertStatus(200);
        $response->assertSee('Susiję dokumentai');
    }

    public function test_legal_show_page_shows_document_metadata(): void
    {
        $legal = Legal::factory()->enabled()->published()->create([
            'key' => 'privacy-policy',
            'meta_data' => [
                'version' => '2.0',
                'effective_date' => '2024-01-01',
                'last_reviewed' => '2024-06-01',
            ],
        ]);

        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'title' => 'Privatumo politika',
            'content' => '<p>Turinys...</p>',
        ]);

        $response = $this->get(route('legal.show', 'privacy-policy'));

        $response->assertStatus(200);
        $response->assertSee('Versija: 2.0');
        $response->assertSee('Įsigaliojimo data: 2024-01-01');
        $response->assertSee('Peržiūros data: 2024-06-01');
    }

    public function test_legal_show_page_shows_reading_time(): void
    {
        $legal = Legal::factory()->enabled()->published()->create(['key' => 'privacy-policy']);
        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'title' => 'Privatumo politika',
            'content' => str_repeat('<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. </p>', 10),
        ]);

        $response = $this->get(route('legal.show', 'privacy-policy'));

        $response->assertStatus(200);
        $response->assertSee('min.');
    }

    public function test_legal_show_page_shows_required_badge(): void
    {
        $legal = Legal::factory()->required()->enabled()->published()->create(['key' => 'privacy-policy']);
        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'lt',
            'title' => 'Privatumo politika',
        ]);

        $response = $this->get(route('legal.show', 'privacy-policy'));

        $response->assertStatus(200);
        $response->assertSee('Privalomas');
    }

    public function test_legal_index_page_groups_documents_by_type(): void
    {
        $privacyPolicy = Legal::factory()->privacyPolicy()->enabled()->published()->create();
        $termsOfUse = Legal::factory()->termsOfUse()->enabled()->published()->create();

        LegalTranslation::factory()->create([
            'legal_id' => $privacyPolicy->id,
            'locale' => 'lt',
            'title' => 'Privatumo politika',
        ]);

        LegalTranslation::factory()->create([
            'legal_id' => $termsOfUse->id,
            'locale' => 'lt',
            'title' => 'Naudojimosi sąlygos',
        ]);

        $response = $this->get(route('legal.index'));

        $response->assertStatus(200);
        $response->assertSee('Privatumo politika');
        $response->assertSee('Naudojimosi sąlygos');
    }

    public function test_legal_show_page_handles_missing_translation(): void
    {
        $legal = Legal::factory()->enabled()->published()->create(['key' => 'privacy-policy']);
        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'en',
            'title' => 'Privacy Policy',
        ]);

        // Set locale to Lithuanian but only English translation exists
        app()->setLocale('lt');

        $response = $this->get(route('legal.show', 'privacy-policy'));

        $response->assertStatus(200);
        $response->assertSee('Privacy Policy'); // Should fallback to English
    }
}
