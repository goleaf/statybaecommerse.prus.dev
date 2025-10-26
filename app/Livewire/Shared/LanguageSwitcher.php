<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;
use Throwable;

/**
 * LanguageSwitcher
 *
 * Livewire component for LanguageSwitcher with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property array<int, string>                                                      $locales
 * @property string                                                                  $current
 * @property array<string, array{locale:string,label:string,url:string,active:bool}> $links
 */
class LanguageSwitcher extends Component
{
    /**
     * @var array<int, string>
     */
    public array $locales = [];

    public string $current;

    /**
     * @var array<string, array{locale:string,label:string,url:string,active:bool}>
     */
    public array $links = [];

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $supported = config('app.supported_locales', ['en']);
        $rawLocales = is_array($supported) ? $supported : explode(',', (string) $supported);

        $this->locales = array_values(array_unique(array_filter(array_map(
            static fn ($locale): string => trim((string) $locale),
            $rawLocales,
        ), static fn (string $locale): bool => $locale !== '')));

        $this->current = app()->getLocale();

        $fullUrl = url()->full();
        $path = (string) (parse_url($fullUrl, PHP_URL_PATH) ?? '/');
        $query = parse_url($fullUrl, PHP_URL_QUERY);
        $queryString = $query ? '?' . $query : '';

        $segments = $path === '/' ? [] : explode('/', trim($path, '/'));
        if ($segments !== [] && in_array($segments[0], $this->locales, true)) {
            array_shift($segments);
        }
        $canonicalPath = implode('/', $segments);
        $canonicalPath = trim($canonicalPath, '/');

        $route = request()->route();
        $routeName = $route?->getName();
        $routeParameters = $route?->parameters() ?? [];

        unset($routeParameters['locale']);

        $links = [];
        foreach ($this->locales as $locale) {
            $target = null;

            if ($routeName && str_starts_with($routeName, 'localized.')) {
                try {
                    $target = route(
                        $routeName,
                        ['locale' => $locale] + $routeParameters,
                    );
                } catch (Throwable) {
                    $target = null;
                }
            }

            if ($target === null) {
                $base = $canonicalPath === '' ? '' : '/' . $canonicalPath;
                $target = url('/' . $locale . $base);
            }

            if ($queryString !== '') {
                $glue = str_contains($target, '?') ? '&' : '?';
                $target .= $glue . ltrim($queryString, '?');
            }

            $links[$locale] = [
                'locale' => $locale,
                'label'  => Str::upper($locale),
                'url'    => $target,
                'active' => $locale === $this->current,
            ];
        }

        $this->links = $links;
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.shared.language-switcher');
    }
}
