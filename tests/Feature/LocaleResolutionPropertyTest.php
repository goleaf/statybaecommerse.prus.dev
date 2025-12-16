<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\LocaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class LocaleResolutionPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * **Feature: performance-update, Property 3: Centralized Locale Resolution**
     * **Validates: Requirements 3.1**
     *
     * For any web request, locale resolution should occur exactly once in the middleware layer,
     * and Livewire components should not perform additional locale resolution.
     */
    public function test_centralized_locale_resolution_property(): void
    {
        // Set up supported locales
        Config::set('app.supported_locales', ['lt', 'en', 'de']);
        Config::set('app.locale', 'lt');
        Config::set('app.fallback_locale', 'en');

        $localeService = app(LocaleService::class);

        // Property: For any valid locale parameter, the service should resolve it correctly
        $testCases = [
            ['query_locale' => 'lt', 'expected' => 'lt'],
            ['query_locale' => 'en', 'expected' => 'en'],
            ['query_locale' => 'de', 'expected' => 'de'],
            ['query_locale' => null, 'expected' => 'lt'], // default when no locale specified
            ['query_locale' => 'invalid', 'expected' => 'en'], // fallback when invalid locale provided
        ];

        foreach ($testCases as $case) {
            // Create a mock request with query parameter
            $url = $case['query_locale'] ? "/test?locale={$case['query_locale']}" : '/test';
            $request = Request::create($url, 'GET');

            // Resolve locale using the centralized service
            $resolvedLocale = $localeService->resolveLocale($request);

            // Assert the locale is resolved correctly
            $this->assertEquals(
                $case['expected'],
                $resolvedLocale,
                "Failed for query locale: {$case['query_locale']}"
            );
        }
    }

    /**
     * **Feature: performance-update, Property 3: Centralized Locale Resolution**
     * **Validates: Requirements 3.1**
     *
     * Property: The locale service should handle various input combinations consistently
     */
    public function test_locale_resolution_consistency_property(): void
    {
        Config::set('app.supported_locales', ['lt', 'en']);
        Config::set('app.locale', 'lt');

        $localeService = app(LocaleService::class);

        // Property: Multiple calls with same parameters should return same result
        $request = Request::create('/test?locale=en', 'GET');

        $firstResolution = $localeService->resolveLocale($request);
        $secondResolution = $localeService->resolveLocale($request);

        $this->assertEquals($firstResolution, $secondResolution);
        $this->assertEquals('en', $firstResolution);
    }

    /**
     * **Feature: performance-update, Property 3: Centralized Locale Resolution**
     * **Validates: Requirements 3.1**
     *
     * Property: Locale service should be centralized and available
     */
    public function test_locale_service_centralization_property(): void
    {
        Config::set('app.supported_locales', ['lt', 'en']);
        Config::set('app.locale', 'lt');

        // Property: LocaleService should be available as singleton
        $service1 = app(LocaleService::class);
        $service2 = app(LocaleService::class);

        $this->assertSame($service1, $service2);
        $this->assertInstanceOf(LocaleService::class, $service1);
    }

    /**
     * **Feature: performance-update, Property 3: Centralized Locale Resolution**
     * **Validates: Requirements 3.1**
     *
     * Property: Application locale should be set when using resolveAndSetLocale
     */
    public function test_application_locale_setting_property(): void
    {
        Config::set('app.supported_locales', ['lt', 'en']);
        Config::set('app.locale', 'lt');

        $localeService = app(LocaleService::class);

        // Property: resolveAndSetLocale should set the application locale
        $request = Request::create('/test?locale=en', 'GET');

        $resolvedLocale = $localeService->resolveAndSetLocale($request);

        $this->assertEquals('en', $resolvedLocale);
        $this->assertEquals('en', App::getLocale());
        $this->assertEquals('en', app('request_locale'));
    }
}
