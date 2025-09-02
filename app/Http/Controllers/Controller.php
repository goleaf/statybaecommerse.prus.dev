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
        return $count === null
            ? __($key, $params)
            : trans_choice($key, $count, $params);
    }

    protected function tArray(array $data): array
    {
        $translateNode = function ($node) use (&$translateNode) {
            if (is_string($node)) {
                return __($node);
            }
            if (is_array($node)) {
                if (array_key_exists('key', $node)) {
                    $key = (string) $node['key'];
                    $params = (array) ($node['params'] ?? []);
                    $count = $node['count'] ?? null;

                    return $count === null ? __($key, $params) : trans_choice($key, (int) $count, $params);
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
