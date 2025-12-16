<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Controller
 *
 * HTTP controller handling Controller related web requests, responses, and business logic with proper validation and error handling.
 */
abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Initialize the class instance with required dependencies.
     *
     * Note: Locale resolution is now handled centrally by SetLocale middleware,
     * removing duplicate locale resolution from individual controllers.
     */
    public function __construct()
    {
        // Locale resolution removed - handled by SetLocale middleware
    }

    /**
     * Handle t functionality with proper error handling.
     */
    protected function t(string $key, array $params = [], ?int $count = null): string
    {
        // Use the new unified translation files (lt.php, en.php)
        $translationKey = $this->normalizeTranslationKey($key);

        return $count === null ? __($translationKey, $params) : trans_choice($translationKey, $count, $params);
    }

    /**
     * Handle normalizeTranslationKey functionality with proper error handling.
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
     * Handle tArray functionality with proper error handling.
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
