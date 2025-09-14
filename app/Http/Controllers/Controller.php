<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

/**
 * Base Controller
 * 
 * Abstract base controller that provides common functionality
 * for all controllers including locale preference handling
 * and translation utilities.
 */
abstract /**
 * Controller
 * 
 * HTTP controller handling web requests and responses.
 */
class Controller
{
    /**
     * Create a new controller instance.
     * 
     * Automatically applies the user's preferred locale
     * during controller initialization.
     */
    public function __construct()
    {
        $this->applyPreferredLocale();
    }

    /**
     * Apply the user's preferred locale.
     * 
     * Determines the user's preferred locale from various sources:
     * 1. User's preferred_locale setting
     * 2. Route locale parameter
     * 3. Request locale parameter
     * 4. Accept-Language header
     * 
     * Only applies if the locale is supported by the application.
     * 
     * @return void
     */
    protected function applyPreferredLocale(): void
    {
        $supported = collect(is_array(config('app.supported_locales')) ? config('app.supported_locales') : explode(',', (string) config('app.supported_locales', 'en')))
            ->map(fn ($l) => trim((string) $l))
            ->filter()
            ->values();

        $preferred = Request::user()?->preferred_locale
            ?: Request::route('locale')
            ?: Request::get('locale')
            ?: substr((string) Request::header('Accept-Language', ''), 0, 2);

        if (is_string($preferred) && $preferred !== '' && $supported->contains($preferred)) {
            App::setLocale($preferred);
        }
    }

    /**
     * Translate a key with parameters and optional pluralization.
     * 
     * Provides a unified translation method that normalizes translation keys
     * and handles both simple translations and pluralized translations.
     * 
     * @param string $key The translation key
     * @param array $params Parameters to replace in the translation
     * @param int|null $count Count for pluralization (null for no pluralization)
     * @return string The translated string
     */
    protected function t(string $key, array $params = [], ?int $count = null): string
    {
        // Use the new unified translation files (lt.php, en.php)
        $translationKey = $this->normalizeTranslationKey($key);

        return $count === null
            ? __($translationKey, $params)
            : trans_choice($translationKey, $count, $params);
    }

    /**
     * Normalize translation key format.
     * 
     * Converts dot notation keys to snake_case format for compatibility
     * with the new translation file structure.
     * 
     * @param string $key The translation key to normalize
     * @return string The normalized translation key
     */
    protected function normalizeTranslationKey(string $key): string
    {
        // Convert dot notation to snake_case for new translation structure
        // e.g., 'nav.home' becomes 'nav_home'
        if (str_contains($key, '.')) {
            return str_replace('.', '_', $key);
        }

        return $key;
    }

    /**
     * Translate an array of data recursively.
     * 
     * Recursively processes an array structure and translates all string values
     * and special translation objects. Handles nested arrays and objects.
     * 
     * @param array $data The data array to translate
     * @return array The translated data array
     */
    protected function tArray(array $data): array
    {
        $translateNode = function ($node) use (&$translateNode) {
            if (is_string($node)) {
                return __($this->normalizeTranslationKey($node));
            }
            if (is_array($node)) {
                if (array_key_exists('key', $node)) {
                    $key = (string) $node['key'];
                    $normalizedKey = $this->normalizeTranslationKey($key);
                    $params = (array) ($node['params'] ?? []);
                    $count = $node['count'] ?? null;

                    return $count === null ? __($normalizedKey, $params) : trans_choice($normalizedKey, (int) $count, $params);
                }
                foreach ($node as $k => $v) {
                    $node[$k] = $translateNode($v);
                }

                return $node;
            }

            return $node;
        };

        return $translateNode($data);
    }
}
