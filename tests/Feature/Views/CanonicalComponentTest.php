<?php

declare(strict_types=1);

namespace Tests\Feature\Views;

use Illuminate\Http\Request;
use Tests\TestCase;

class CanonicalComponentTest extends TestCase
{
    public function test_component_filters_tracking_query_parameters(): void
    {
        app()->setLocale('en');

        $queryString = 'utm_source=newsletter&fbclid=abc123&color=red&color=red&page=2&sort=price';

        // Create a synthetic request so the Blade component has deterministic URL context for the assertions.
        $request = Request::create('/en/test-canonical?' . $queryString);
        $request->server->set('QUERY_STRING', $queryString);
        $request->server->set('REQUEST_URI', '/en/test-canonical?' . $queryString);
        app()->instance('request', $request);

        $view = $this->blade('<x-canonical />');

        // The canonical link should drop tracking params, deduplicate identical pairs, and sort remaining parameters.
        $view->assertSee(
            '<link rel="canonical" href="http://localhost/en/test-canonical?color=red&amp;page=2&amp;sort=price" />',
            false
        );
    }

    public function test_component_excludes_query_string_when_every_parameter_is_filtered(): void
    {
        app()->setLocale('en');

        $queryString = 'utm_medium=email&gclid=abc123';

        // Provide a request containing only ignored parameters so the canonical link collapses to the base URL.
        $request = Request::create('/en/test-canonical?' . $queryString);
        $request->server->set('QUERY_STRING', $queryString);
        $request->server->set('REQUEST_URI', '/en/test-canonical?' . $queryString);
        app()->instance('request', $request);

        $view = $this->blade('<x-canonical />');

        // When all parameters are filtered out, the canonical URL should not include a dangling question mark.
        $view->assertSee('<link rel="canonical" href="http://localhost/en/test-canonical" />', false);
    }
}
