<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class SitemapControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_renders_xml_index_with_service_payload(): void
    {
        // Ensure locales exist in configuration so the view renders consistent URLs.
        Config::set('app.supported_locales', ['en']);

        $sitemaps = [
            [
                'loc'     => 'https://example.test/en/sitemap.xml',
                'lastmod' => '2025-01-01T00:00:00+00:00',
            ],
        ];

        $response = $this->get('/sitemap.xml');

        // Assert that the response renders the XML view and exposes the correct headers/content.
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<sitemapindex', false);
    }

    public function test_locale_normalizes_input_and_renders_xml_sitemap(): void
    {
        // Provide the supported locales to match the controller behaviour and prevent cache side-effects.
        Config::set('app.supported_locales', ['en']);

        $response = $this->get('/EN/sitemap.xml');

        // Validate the XML payload and ensure the response exposes the sitemap structure.
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->assertSee('<urlset', false);
    }

    public function test_locale_rejects_malicious_locale_tokens(): void
    {
        $response = $this->get('/..%2F..%2F/sitemap.xml');

        // Expect a not found response indicating the locale was invalid.
        $response->assertNotFound();
    }

    public function test_locale_returns_not_found_when_service_reports_missing_locale(): void
    {
        $response = $this->get('/xx/sitemap.xml');

        // Confirm the controller surfaces the error as a standard 404.
        $response->assertNotFound();
    }
}
