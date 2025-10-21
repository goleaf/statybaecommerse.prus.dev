<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class FrontendStaticRoutesTest extends TestCase
{
    public function test_sitemap_route_renders_successfully(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
    }

    public function test_robots_route_renders_successfully(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
    }
}
