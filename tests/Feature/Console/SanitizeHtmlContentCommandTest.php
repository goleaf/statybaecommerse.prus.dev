<?php declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Product;
use App\Models\Translations\LegalTranslation;
use App\Models\Translations\ProductTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SanitizeHtmlContentCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resanitizes_legacy_records(): void
    {
        $product = Product::factory()->create([
            'description' => '<p>Safe</p><script>alert(1)</script>',
        ]);

        $productTranslation = ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'description' => '<div><iframe src="https://example.com"></iframe><p>Translated</p></div>',
        ]);

        $legalTranslation = LegalTranslation::factory()->create([
            'content' => '<style>body{background:red;}</style><p>Terms</p>',
        ]);

        $this->artisan('maintenance:sanitize-html')->assertExitCode(0);

        $this->assertSame('<p>Safe</p>', $product->fresh()->description);
        $this->assertSame('<div><p>Translated</p></div>', $productTranslation->fresh()->description);
        $this->assertSame('<p>Terms</p>', $legalTranslation->fresh()->content);
    }
}
