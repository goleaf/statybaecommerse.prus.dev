<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Support\Locales;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

final class LanguageSwitcher extends Component
{
    /**
     * @var array<string, array{name?: string, native?: string, flag?: string, direction?: string}>
     */
    public array $supportedLocales = [];

    /**
     * @var array<string, string>
     */
    public array $localeLinks = [];

    public string $currentLocale;

    public function __construct()
    {
        $this->currentLocale = app()->getLocale();

        $localeConfig = config('app.locales', []);
        $this->supportedLocales = array_intersect_key(
            $localeConfig,
            array_flip(Locales::supported())
        );

        $this->localeLinks = $this->buildLocaleLinks();
    }

    public function render(): View
    {
        return view('components.language-switcher');
    }

    /**
     * @return array<string, string>
     */
    private function buildLocaleLinks(): array
    {
        $route = request()->route();
        $links = [];

        foreach (array_keys($this->supportedLocales) as $locale) {
            $targetUrl = null;

            if ($route && ($name = $route->getName()) && str_starts_with($name, 'localized.')) {
                $parameters = $route->parameters();
                $parameters['locale'] = $locale;
                $targetUrl = route($name, $parameters, false);
            } elseif (Route::has('localized.home')) {
                $targetUrl = route('localized.home', ['locale' => $locale], false);
            }

            $links[$locale] = $targetUrl ?? url('/' . ltrim($locale, '/'));
        }

        return $links;
    }
}
