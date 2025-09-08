<?php declare(strict_types=1);

it('serves robots.txt with sitemaps per locale', function (): void {
    config()->set('app.url', 'https://example.com');
    config()->set('app.supported_locales', 'en,fr');

    $resp = $this->get('/robots.txt');

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('text/plain');
    $resp->assertSee('Disallow: /cpanel/');
    $resp->assertSee('Sitemap: https://example.com/en/sitemap.xml');
    $resp->assertSee('Sitemap: https://example.com/fr/sitemap.xml');
});


