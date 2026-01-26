<?php

declare(strict_types=1);

namespace App\View\Components\Filament;

use App\Support\Locales;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

final class LanguageSwitcher extends Component
{
    /**
     * @var array<string, string>
     */
    public array $availableLocales = [];

    /**
     * @var array<string, string>
     */
    public array $localeLinks = [];

    public string $currentLocale;

    public function __construct()
    {
        $this->currentLocale = app()->getLocale();
        $this->availableLocales = $this->buildAvailableLocales();
        $this->localeLinks = $this->buildLocaleLinks();
    }

    public function render(): View
    {
        return view('filament.components.language-switcher');
    }

    /**
     * @return array<string, string>
     */
    private function buildAvailableLocales(): array
    {
        $locales = config('app.locales', []);
        $supported = Locales::supported();
        $available = [];

        foreach ($supported as $locale) {
            $label = __('admin.language_switcher.locales.' . $locale);
            if ($label === 'admin.language_switcher.locales.' . $locale) {
                $label = $locales[$locale]['native'] ?? $locales[$locale]['name'] ?? strtoupper($locale);
            }
            $available[$locale] = $label;
        }

        return $available;
    }

    /**
     * @return array<string, string>
     */
    private function buildLocaleLinks(): array
    {
        $route = request()->route();
        $links = [];

        foreach (array_keys($this->availableLocales) as $locale) {
            $targetUrl = null;

            if ($route && ($name = $route->getName()) && str_starts_with($name, 'localized.')) {
                $parameters = $route->parameters();
                $parameters['locale'] = $locale;
                $targetUrl = route($name, $parameters, true);
            } elseif (Route::has('localized.home')) {
                $targetUrl = route('localized.home', ['locale' => $locale], true);
            }

            $links[$locale] = $targetUrl ?? url('/' . ltrim($locale, '/'));
        }

        return $links;
    }
}
