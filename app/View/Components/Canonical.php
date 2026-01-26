<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Support\Locales;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

final class Canonical extends Component
{
    public string $href;

    public function __construct()
    {
        $this->href = $this->buildCanonical();
    }

    public function render(): View
    {
        return view('components.canonical');
    }

    private function buildCanonical(): string
    {
        $full = url()->full();
        $path = parse_url($full, PHP_URL_PATH) ?? '/';
        $qs = parse_url($full, PHP_URL_QUERY);
        $query = '';

        if ($qs) {
            $query = $this->normalizeQueryString($qs);
        }

        $locales = Locales::supported();
        $parts = explode('/', ltrim($path, '/'));
        if (isset($parts[0]) && in_array($parts[0], $locales, true)) {
            array_shift($parts);
        }

        $rest = trim(implode('/', $parts), '/');
        $canonical = $rest === '' ? url('/' . app()->getLocale()) : url('/' . app()->getLocale() . '/' . $rest);

        return $canonical . $query;
    }

    private function normalizeQueryString(string $qs): string
    {
        $ignoredPrefixes = ['utm_', 'pk_', 'mc_', 'ga_'];
        $ignoredKeys = ['fbclid', 'gclid', 'yclid', 'ref', 'source'];

        $pairs = collect(explode('&', $qs))
            ->map(static fn (string $pair): string => trim($pair))
            ->filter(static fn (string $pair): bool => $pair !== '');

        $filteredPairs = $pairs
            ->map(static function (string $pair): array {
                [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
                $normalizedKey = strtolower($key);

                return [
                    'original' => $pair,
                    'key' => $key,
                    'value' => $value,
                    'normalized_key' => $normalizedKey,
                ];
            })
            ->filter(static function (array $pair) use ($ignoredPrefixes, $ignoredKeys): bool {
                $key = $pair['normalized_key'];

                if ($key === '') {
                    return false;
                }

                if (in_array($key, $ignoredKeys, true)) {
                    return false;
                }

                foreach ($ignoredPrefixes as $prefix) {
                    if (str_starts_with($key, $prefix)) {
                        return false;
                    }
                }

                return true;
            })
            ->unique(static fn (array $pair): string => $pair['normalized_key'] . '=' . $pair['value'])
            ->sortBy(static fn (array $pair): string => $pair['normalized_key'] . '=' . $pair['value'])
            ->map(static function (array $pair): string {
                $value = $pair['value'];

                return $pair['key'] . ($value !== '' ? '=' . $value : '');
            })
            ->values();

        if ($filteredPairs->isEmpty()) {
            return '';
        }

        return '?' . $filteredPairs->implode('&');
    }
}
