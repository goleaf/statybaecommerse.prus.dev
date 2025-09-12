<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

abstract class Controller
{
    public function __construct()
    {
        $this->applyPreferredLocale();
    }

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

    protected function t(string $key, array $params = [], ?int $count = null): string
    {
        // Use the new unified translation files (lt.php, en.php)
        $translationKey = $this->normalizeTranslationKey($key);

        return $count === null
            ? __($translationKey, $params)
            : trans_choice($translationKey, $count, $params);
    }

    protected function normalizeTranslationKey(string $key): string
    {
        // Convert dot notation to snake_case for new translation structure
        // e.g., 'nav.home' becomes 'nav_home'
        if (str_contains($key, '.')) {
            return str_replace('.', '_', $key);
        }

        return $key;
    }

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
