<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use Tests\TestCase;

final class ApiDocsControllerTest extends TestCase
{
    public function test_it_renders_the_api_docs_view_successfully(): void
    {
        // Request the documented API endpoint so the controller is exercised end-to-end.
        $response = $this->get(route('docs.api'));

        // The controller should respond with a 200 status when the documentation page renders correctly.
        $response->assertOk();

        // Ensure the expected Blade view is returned so template regressions are caught quickly.
        $response->assertViewIs('docs.api');
    }
}
