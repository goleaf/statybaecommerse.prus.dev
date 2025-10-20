<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Services\Security\HtmlContentSanitizer;
use Tests\Feature\TestCase;

final class HtmlContentSanitizerTest extends TestCase
{
    public function test_it_allows_expected_rich_text_elements(): void
    {
        $sanitizer = new HtmlContentSanitizer;

        $html = '<h2>Heading</h2><p><strong>Bold</strong> <a href="https://example.com" target="_blank">link</a></p><ul><li>Item</li></ul>';
        $sanitized = $sanitizer->sanitize($html);

        self::assertSame('<h2>Heading</h2><p><strong>Bold</strong> <a href="https://example.com" target="_blank" rel="noopener noreferrer">link</a></p><ul><li>Item</li></ul>', $sanitized);
    }

    public function test_it_strips_script_injection(): void
    {
        $sanitizer = app(HtmlContentSanitizer::class);

        $html = '<h3>Intro</h3><script>alert(1)</script>';
        $sanitized = $sanitizer->sanitize($html);

        self::assertSame('<h3>Intro</h3>', $sanitized);
    }

    public function test_it_removes_javascript_links(): void
    {
        $sanitizer = app(HtmlContentSanitizer::class);

        $html = '<p><a href="javascript:alert(1)">Click me</a></p>';
        $sanitized = $sanitizer->sanitize($html);

        self::assertSame('<p><a>Click me</a></p>', $sanitized);
    }

    public function test_it_enforces_lazy_loading_on_images(): void
    {
        $sanitizer = app(HtmlContentSanitizer::class);

        $html = '<p><img src="https://cdn.example.com/image.jpg" onerror="alert(1)" loading="eager"></p>';
        $sanitized = $sanitizer->sanitize($html);

        self::assertSame('<p><img src="https://cdn.example.com/image.jpg" loading="lazy"></p>', $sanitized);
    }

    public function test_it_drops_untrusted_iframe_hosts(): void
    {
        $sanitizer = app(HtmlContentSanitizer::class);

        $html = '<iframe src="https://evil.example.com/embed"></iframe>';
        $sanitized = $sanitizer->sanitize($html);

        self::assertSame('', $sanitized);
    }

    public function test_it_allows_trusted_iframes(): void
    {
        $sanitizer = app(HtmlContentSanitizer::class);

        $html = '<iframe src="https://www.youtube.com/embed/xyz" allow="autoplay"></iframe>';
        $sanitized = $sanitizer->sanitize($html);

        self::assertSame('<iframe src="https://www.youtube.com/embed/xyz" allow="autoplay" loading="lazy"></iframe>', $sanitized);
    }
}
