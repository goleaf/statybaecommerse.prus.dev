<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('serves robots.txt', function (): void {
    // Exercise the endpoint with the default configuration to ensure the response is returned successfully.
    $response = get('/robots.txt');

    // Assert the response is ok and the content type is set to plain text for robots.txt consumers.
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/plain');
});

it('generates sitemap entries using the configured app url scheme and host', function (): void {
    // Configure a custom application URL so that the controller must respect both scheme and host components.
    config()->set('app.url', 'https://example.test');
    config()->set('app.supported_locales', 'en,lt');

    // Invoke the controller and inspect the rendered robots.txt response body.
    $response = get('/robots.txt');

    // Confirm the sitemap entries follow the configured scheme/host combination for every locale.
    $response->assertOk();
    $response->assertSeeInOrder([
        'Sitemap: https://example.test/en/sitemap.xml',
        'Sitemap: https://example.test/lt/sitemap.xml',
    ]);
});

it('falls back to the request context and de-duplicates locales provided as an array', function (): void {
    // Simulate missing configuration and duplicated locales presented as an array value.
    config()->set('app.url', null);
    config()->set('app.supported_locales', ['lt', 'en', 'lt', '']);

    // Call the endpoint so that the controller has to infer host/scheme from the request.
    $response = get('/robots.txt');

    // Validate that the robots body prefers the request information and contains unique sitemap entries.
    $response->assertOk();
    $response->assertSeeInOrder([
        'Sitemap: http://localhost/lt/sitemap.xml',
        'Sitemap: http://localhost/en/sitemap.xml',
    ]);
    $response->assertDontSee('Sitemap: http://localhost//sitemap.xml');
});
