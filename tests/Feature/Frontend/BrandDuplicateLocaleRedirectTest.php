<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use Tests\TestCase;

final class BrandDuplicateLocaleRedirectTest extends TestCase
{
    public function test_duplicate_locale_brands_index_redirects_to_single_locale_path(): void
    {
        $response = $this->get('/lt/lt/brands?page=3');

        $response->assertStatus(301)
            ->assertRedirect('/lt/brands?page=3');
    }

    public function test_duplicate_locale_brand_show_redirects_to_single_locale_path(): void
    {
        $response = $this->get('/lt/lt/brands/sample-brand');

        $response->assertStatus(301)
            ->assertRedirect('/lt/brands/sample-brand');
    }
}
