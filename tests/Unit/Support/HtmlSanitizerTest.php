<?php declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Html\HtmlSanitizer;
use Tests\TestCase;

final class HtmlSanitizerTest extends TestCase
{
    private HtmlSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sanitizer = new HtmlSanitizer();
    }

    public function test_it_strips_dangerous_elements(): void
    {
        $input = '<p>Safe</p><script>alert(1)</script><iframe src="https://example.com"></iframe>';
        $this->assertSame('<p>Safe</p>', $this->sanitizer->sanitize($input));
    }

    public function test_it_removes_disallowed_attributes_and_schemes(): void
    {
        $input = '<a href="javascript:alert(1)" onclick="doBad()">Click</a>';
        $this->assertSame('<a>Click</a>', $this->sanitizer->sanitize($input));
    }

    public function test_it_normalises_styles_and_discards_unknown_properties(): void
    {
        $input = '<p style="color: #FF0000; position:absolute; text-align: center">Test</p>';
        $this->assertSame('<p style="color: #ff0000; text-align: center">Test</p>', $this->sanitizer->sanitize($input));
    }

    public function test_it_preserves_allowed_table_markup(): void
    {
        $input = '<table><tr><th scope="col">Heading</th><td colspan="2">Cell</td></tr></table>';
        $expected = '<table><tr><th scope="col">Heading</th><td colspan="2">Cell</td></tr></table>';

        $this->assertSame($expected, $this->sanitizer->sanitize($input));
    }
}
