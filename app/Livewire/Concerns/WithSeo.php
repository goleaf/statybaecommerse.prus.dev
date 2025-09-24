<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Support\Helpers\SharedHelpers;

/**
 * WithSeo
 *
 * Trait providing reusable functionality across multiple classes.
 */
trait WithSeo
{
    public function getSeoTitle(string $title, ?string $suffix = null): string
    {
        $siteName = config('app.name');
        $fullTitle = $suffix ? "{$title} - {$suffix}" : $title;

        return SharedHelpers::getSeoTitle($fullTitle, $siteName);
    }

    public function getSeoDescription(string $content, int $length = 160): string
    {
        return SharedHelpers::getSeoDescription($content, $length);
    }

    public function getCanonicalUrl(): string
    {
        return url()->current();
    }

    public function getLocalizedUrls(): array
    {
        $urls = [];
        $supportedLocales = ['lt', 'en', 'de'];
        $currentRoute = request()->route();
        if (! $currentRoute) {
            return $urls;
        }
        foreach ($supportedLocales as $locale) {
            $parameters = array_merge($currentRoute->parameters(), ['locale' => $locale]);
            $urls[$locale] = route($currentRoute->getName(), $parameters);
        }

        return $urls;
    }

    public function getStructuredData(): array
    {
        return ['@context' => 'https://schema.org', '@type' => 'WebPage', 'url' => $this->getCanonicalUrl(), 'name' => $this->getSeoTitle(''), 'description' => $this->getSeoDescription('')];
    }
}
