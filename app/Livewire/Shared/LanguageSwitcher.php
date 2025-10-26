<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use App\Data\Storefront\Shared\LanguageLinkData;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTags;
use App\Support\Cache\TagAwareCache;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * LanguageSwitcher
 *
 * Livewire component for LanguageSwitcher with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property array<int, string>                                                          $locales
 * @property string                                                                      $current
 * @property array<string, array{locale:string,label:string,url:string,active:bool}>     $languages
 */
class LanguageSwitcher extends Component
{
    /**
     * @var array<int, string>
     */
    public array $locales = [];

    public string $current;

    /**
     * Store the typed link entries privately to avoid Livewire hydration issues.
     *
     * @var array<string, LanguageLinkData>
     */
    private array $languageEntries = [];

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $supported = config('app.supported_locales', ['en']);
        $rawLocales = is_array($supported) ? $supported : explode(',', (string) $supported);
        $this->locales = array_values(array_filter(array_map(
            static fn ($locale): string => trim((string) $locale),
            $rawLocales
        ), static fn (string $locale): bool => $locale !== ''));
        $this->current = app()->getLocale();

        $full = url()->full();
        $path = (string) (parse_url($full, PHP_URL_PATH) ?? '/');
        $query = parse_url($full, PHP_URL_QUERY);
        $queryString = $query ? '?' . $query : '';

        $parts = explode('/', ltrim($path, '/'));
        if ($parts !== [] && $parts[0] !== '' && in_array($parts[0], $this->locales, true)) {
            array_shift($parts);
        }
        $rest = trim(implode('/', $parts), '/');

        $signature = hash('sha256', $rest . '|' . $queryString . '|' . implode(',', $this->locales));

        /** @var array<string, array{locale:string,label:string,url:string,active:bool}> $payload */
        $payload = TagAwareCache::remember(
            CacheKeys::languageSwitcherLinks($this->current, $signature),
            now()->addMinutes(5),
            function () use ($rest, $queryString): array {
                $links = [];

                foreach ($this->locales as $locale) {
                    $href = $rest === '' ? url('/' . $locale) : url('/' . $locale . '/' . $rest);

                    $links[$locale] = (new LanguageLinkData(
                        $locale,
                        Str::upper($locale),
                        $href . $queryString,
                        $locale === $this->current,
                    ))->toArray();
                }

                return $links;
            },
            [
                CacheTags::locale($this->current),
                CacheTags::settings(),
            ]
        );

        $this->languageEntries = array_map(
            static fn (array $entry): LanguageLinkData => LanguageLinkData::fromArray($entry),
            $payload,
        );
    }

    /**
     * Provide the language links as primitive arrays for use in Blade.
     *
     * @return array<string, array{locale:string,label:string,url:string,active:bool}>
     */
    public function getLanguagesProperty(): array
    {
        return array_map(
            static fn (LanguageLinkData $link): array => $link->toArray(),
            $this->languageEntries,
        );
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.shared.language-switcher');
    }
}
