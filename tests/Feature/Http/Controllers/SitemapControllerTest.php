<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Mockery\MockInterface;
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

        // Mock the sitemap service to provide a deterministic payload for the response.
        $this->mock(SitemapService::class, function (MockInterface $mock) use ($sitemaps): void {
            $mock->shouldReceive('getIndex')->once()->andReturn($sitemaps);
        });

        $response = $this->get('/sitemap.xml');

        // Assert that the response renders the XML view and exposes the correct headers/content.
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<loc>https://example.test/en/sitemap.xml</loc>', false);
    }

    public function test_locale_normalizes_input_and_renders_xml_sitemap(): void
    {
        // Provide the supported locales to match the controller behaviour and prevent cache side-effects.
        Config::set('app.supported_locales', ['en']);

        $urls = [
            [
                'loc'        => 'https://example.test/en/products/example',
                'lastmod'    => '2025-01-01T00:00:00+00:00',
                'changefreq' => 'weekly',
                'priority'   => '0.8',
                'alternates' => ['en' => 'https://example.test/en/products/example'],
            ],
        ];

        // Expect the service to receive the sanitized locale and return the prepared payload.
        $this->mock(SitemapService::class, function (MockInterface $mock) use ($urls): void {
            $mock->shouldReceive('getLocaleUrls')->once()->with('en')->andReturn($urls);
        });

        $response = $this->get('/EN/sitemap.xml');

        // Validate the XML payload and ensure the response exposes the sitemap structure.
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->assertSee('<urlset', false);
        $response->assertSee('<loc>https://example.test/en/products/example</loc>', false);
        $response->assertSee('<priority>0.8</priority>', false);
    }

    public function test_locale_rejects_malicious_locale_tokens(): void
    {
        // The service should never be invoked when the locale is stripped to an empty value.
        $this->mock(SitemapService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('getLocaleUrls');
        });

        $response = $this->get('/..%2F..%2F/sitemap.xml');

        // Expect a not found response indicating the locale was invalid.
        $response->assertNotFound();
    }

    public function test_locale_returns_not_found_when_service_reports_missing_locale(): void
    {
        // Configure the service to simulate a locale mismatch via an InvalidArgumentException.
        $this->mock(SitemapService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getLocaleUrls')
                ->once()
                ->with('xx')
                ->andThrow(new InvalidArgumentException('Unsupported locale'));
        });

        $response = $this->get('/xx/sitemap.xml');

        // Confirm the controller surfaces the error as a standard 404.
        $response->assertNotFound();
    }
}
