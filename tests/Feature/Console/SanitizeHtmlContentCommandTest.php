<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Legal;
use App\Models\Product;
use App\Models\Translations\LegalTranslation;
use App\Models\Translations\ProductTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SanitizeHtmlContentCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sanitizes_products_and_legal_translations(): void
    {
        // Seed intentionally unsafe markup across product and translation records.
        $product = Product::factory()->create([
            'description' => '<p>Good</p><script>alert(1)</script>',
            'short_description' => '<span onclick="doBad()">Summary</span>',
            'is_visible' => true,
            'status' => 'active',
            'published_at' => now(),
        ]);

        ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'locale' => 'en',
            'description' => '<div><iframe src="https://evil.test"></iframe><p>Trusted</p></div>',
            'short_description' => '<span style="color:red" onclick="doBad()">Short</span>',
            'summary' => '<section data-test="ok">Summary<script>alert(1)</script></section>',
        ]);

        $legal = Legal::factory()->create();

        LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale' => 'en',
            'content' => '<section><h2>Title</h2><script>alert(1)</script><p data-safe="1">Body</p></section>',
        ]);

        $this->artisan('maintenance:sanitize-html')->assertExitCode(0);

        $product->refresh();
        $this->assertSame('<p>Good</p>', $product->description);
        $this->assertSame('<span>Summary</span>', $product->short_description);

        $translation = ProductTranslation::firstWhere('product_id', $product->id);
        $this->assertNotNull($translation);
        $this->assertSame('<div><p>Trusted</p></div>', $translation->description);
        $this->assertSame('<span>Short</span>', $translation->short_description);
        $this->assertSame('Summary', $translation->summary);

        $legalTranslation = LegalTranslation::firstWhere('legal_id', $legal->id);
        $this->assertNotNull($legalTranslation);
        $this->assertSame('<h2>Title</h2><p data-safe="1">Body</p>', $legalTranslation->content);
    }
}
