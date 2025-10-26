<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * LanguageController
 *
 * HTTP controller handling LanguageController related web requests, responses, and business logic with proper validation and error handling.
 */
final class LanguageController extends Controller
{
    /**
     * Handle switch functionality with proper error handling.
     */
    public function switch(Request $request): RedirectResponse
    {
        // Retrieve the requested locale as a trimmed string to avoid unexpected types.
        $requestedLocale = (string) $request->input('locale', '');

        // Resolve supported locales from configuration while gracefully handling string-based definitions.
        $configuredSupportedLocales = config('app.supported_locales', ['lt', 'en']);
        if (! is_array($configuredSupportedLocales)) {
            // Split comma-separated strings and normalise whitespace if the configuration is not an array.
            $configuredSupportedLocales = explode(',', (string) $configuredSupportedLocales);
        }

        // Normalise the supported locales by trimming values, dropping empties, and ensuring uniqueness.
        $supportedLocales = array_values(array_unique(array_filter(array_map(
            static function ($value): ?string {
                // Only keep values that are non-empty strings.
                if (! is_string($value)) {
                    return null;
                }

                $trimmedValue = trim($value);

                return $trimmedValue !== '' ? $trimmedValue : null;
            },
            $configuredSupportedLocales
        ))));

        // Determine the fallback locale and guarantee it is present within the supported list.
        $fallbackLocale = (string) config('app.locale', 'lt');
        $fallbackLocale = $fallbackLocale !== '' ? $fallbackLocale : 'lt';
        if (! in_array($fallbackLocale, $supportedLocales, true)) {
            $supportedLocales[] = $fallbackLocale;
        }

        // Decide which locale should be applied (requested if supported, otherwise fallback).
        $isSupported = in_array($requestedLocale, $supportedLocales, true);
        $resolvedLocale = $isSupported ? $requestedLocale : $fallbackLocale;

        // Log unsupported locale attempts to aid in debugging while keeping user experience consistent.
        if (! $isSupported && $requestedLocale !== '') {
            Log::warning('Attempt to switch to unsupported locale.', [
                'requested_locale' => $requestedLocale,
                'resolved_locale'  => $resolvedLocale,
                'supported_locales' => $supportedLocales,
            ]);
        }

        // Persist the resolved locale in the session so the preference survives subsequent requests.
        Session::put('locale', $resolvedLocale);

        // Immediately apply the resolved locale to the current request lifecycle.
        app()->setLocale($resolvedLocale);

        // Provide user feedback and ensure we can still redirect even without a referrer header.
        $flashLevel = $isSupported ? 'success' : 'warning';

        return redirect()->back(fallback: url('/'))->with($flashLevel, __('admin.messages.language_changed'));
    }
}
