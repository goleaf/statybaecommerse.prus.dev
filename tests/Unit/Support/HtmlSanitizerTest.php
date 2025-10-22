<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Html\HtmlSanitizer;
use Tests\TestCase;

final class HtmlSanitizerTest extends TestCase
{
    public function test_it_removes_disallowed_elements_and_attributes(): void
    {
        $sanitizer = new HtmlSanitizer;

        $dirty = '<p>Safe<script>alert(1)</script><span onclick="doBad()">text</span></p>';
        $clean = $sanitizer->sanitize($dirty);

        // The script tag and inline handler must be stripped while keeping semantic markup intact.
        $this->assertSame('<p>Safe<span>text</span></p>', $clean);
    }

    public function test_it_preserves_safe_links_with_required_rel_attributes(): void
    {
        $sanitizer = new HtmlSanitizer;

        $dirty = '<a href="https://example.com" rel="nofollow" target="_blank">Link</a>';
        $clean = $sanitizer->sanitize($dirty);

        $this->assertSame('<a href="https://example.com" rel="nofollow noopener noreferrer" target="_blank">Link</a>', $clean);
    }

    public function test_it_drops_unsafe_urls(): void
    {
        $sanitizer = new HtmlSanitizer;

        $dirty = '<a href="javascript:alert(1)">Click</a><img src="data:text/plain;base64,abcd">';
        $clean = $sanitizer->sanitize($dirty);

        // Unsafe protocols should be removed while valid image schemes remain untouched.
        $this->assertSame('<a>Click</a>', $clean);
    }
}
