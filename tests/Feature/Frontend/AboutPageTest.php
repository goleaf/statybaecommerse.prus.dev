<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use Tests\TestCase;

final class AboutPageTest extends TestCase
{
    public function test_localized_about_page_renders_successfully(): void
    {
        $response = $this->get('/lt/about');

        $response->assertSuccessful()
            ->assertSee('Metų patirtis');
    }
}
