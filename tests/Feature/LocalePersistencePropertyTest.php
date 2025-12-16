<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\LocaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LocalePersistencePropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * **Feature: performance-update, Property 4: Locale Persistence Optimization**
     * **Validates: Requirements 3.2**
     *
     * For any request where the resolved locale matches the current locale,
     * no session writes or cookie updates should be triggered.
     */
    public function test_locale_persistence_optimization_property(): void
    {
        Config::set('app.supported_locales', ['lt', 'en']);
        Config::set('app.locale', 'lt');

        $localeService = app(LocaleService::class);

        // Property: When locale hasn't changed, persistence should return false
        $request = Request::create('/test?locale=en', 'GET');

        // Set up existing session and cookie values
        Session::put('locale', 'en');
        $request->cookies->set('app_locale', 'en');

        // First call should not persist (locale hasn't changed)
        $shouldPersist = $localeService->persistLocale('en', $request);

        $this->assertFalse($shouldPersist, 'Should not persist when locale unchanged');
    }

    /**
     * **Feature: performance-update, Property 4: Locale Persistence Optimization**
     * **Validates: Requirements 3.2**
     *
     * Property: When locale changes, persistence should occur
     */
    public function test_locale_persistence_when_changed_property(): void
    {
        Config::set('app.supported_locales', ['lt', 'en']);
        Config::set('app.locale', 'lt');

        $localeService = app(LocaleService::class);

        // Property: When locale changes, persistence should return true
        $request = Request::create('/test', 'GET');

        // Set up existing session with different locale
        Session::put('locale', 'lt');
        $request->cookies->set('app_locale', 'lt');

        // Changing to 'en' should trigger persistence
        $shouldPersist = $localeService->persistLocale('en', $request);

        $this->assertTrue($shouldPersist, 'Should persist when locale changes');
        $this->assertEquals('en', Session::get('locale'));
        $this->assertEquals('en', Session::get('app.locale'));
    }

    /**
     * **Feature: performance-update, Property 4: Locale Persistence Optimization**
     * **Validates: Requirements 3.2**
     *
     * Property: Session mismatch should trigger persistence
     */
    public function test_session_mismatch_triggers_persistence_property(): void
    {
        Config::set('app.supported_locales', ['lt', 'en']);
        Config::set('app.locale', 'lt');

        $localeService = app(LocaleService::class);

        // Property: When session and cookie don't match, persistence should occur
        $request = Request::create('/test', 'GET');

        // Set up mismatched session and cookie
        Session::put('locale', 'lt');
        $request->cookies->set('app_locale', 'en');

        // Should persist to synchronize session and cookie
        $shouldPersist = $localeService->persistLocale('en', $request);

        $this->assertTrue($shouldPersist, 'Should persist when session and cookie mismatch');
    }

    /**
     * **Feature: performance-update, Property 4: Locale Persistence Optimization**
     * **Validates: Requirements 3.2**
     *
     * Property: Empty session should trigger persistence
     */
    public function test_empty_session_triggers_persistence_property(): void
    {
        Config::set('app.supported_locales', ['lt', 'en']);
        Config::set('app.locale', 'lt');

        $localeService = app(LocaleService::class);

        // Property: When session is empty, persistence should occur
        $request = Request::create('/test', 'GET');

        // Clear session
        Session::forget('locale');

        // Should persist when session is empty
        $shouldPersist = $localeService->persistLocale('en', $request);

        $this->assertTrue($shouldPersist, 'Should persist when session is empty');
        $this->assertEquals('en', Session::get('locale'));
    }

    /**
     * **Feature: performance-update, Property 4: Locale Persistence Optimization**
     * **Validates: Requirements 3.2**
     *
     * Property: Persistence behavior should be consistent across multiple calls
     */
    public function test_persistence_consistency_property(): void
    {
        Config::set('app.supported_locales', ['lt', 'en']);
        Config::set('app.locale', 'lt');

        $localeService = app(LocaleService::class);

        // Property: Multiple calls with same state should have consistent behavior
        $request = Request::create('/test', 'GET');

        // Set up initial state
        Session::put('locale', 'en');
        $request->cookies->set('app_locale', 'en');

        // Multiple calls with same locale should all return false
        $result1 = $localeService->persistLocale('en', $request);
        $result2 = $localeService->persistLocale('en', $request);
        $result3 = $localeService->persistLocale('en', $request);

        $this->assertFalse($result1);
        $this->assertFalse($result2);
        $this->assertFalse($result3);
        $this->assertEquals($result1, $result2);
        $this->assertEquals($result2, $result3);
    }
}
