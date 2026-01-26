<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Support\Locales;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Breadcrumbs extends Component
{
    /**
     * @var array<int, array{label: string, url?: string}>
     */
    public array $items;

    /**
     * @var array<string, string>
     */
    public array $breadcrumbs = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $ldItems = [];

    public string $locale;

    /**
     * @param array<int, array{label: string, url?: string}> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
        $this->locale = $this->resolveLocale();
        app()->setLocale($this->locale);

        $this->breadcrumbs = $this->buildBreadcrumbs();
        $this->ldItems = $this->buildLdItems();
    }

    public function render(): View
    {
        return view('components.breadcrumbs');
    }

    private function resolveLocale(): string
    {
        $supportedLocales = Locales::supported();
        $request = request();
        $defaultLocale = config('app.locale', 'lt');

        $candidateLocales = array_values(array_filter([
            $request->route('locale'),
            $request->query('locale'),
            session('locale'),
            session('app.locale'),
            $request->cookie('app_locale'),
            auth()->check() ? (auth()->user()->preferred_locale ?? null) : null,
        ], static fn ($candidate): bool => is_string($candidate) && $candidate !== ''));

        foreach ($candidateLocales as $candidate) {
            if (in_array($candidate, $supportedLocales, true)) {
                return $candidate;
            }
        }

        $fallbackLocale = config('app.fallback_locale', $defaultLocale);

        if (in_array($fallbackLocale, $supportedLocales, true)) {
            return $fallbackLocale;
        }

        return $supportedLocales[0] ?? $defaultLocale;
    }

    /**
     * @return array<string, string>
     */
    private function buildBreadcrumbs(): array
    {
        return collect([['label' => __('messages.frontend), 'url' => url('/' . $this->locale)]])
            ->merge($this->items)
            ->mapWithKeys(static function (array $item): array {
                return [$item['url'] ?? '' => $item['label']];
            })
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildLdItems(): array
    {
        $ldItems = [];
        $pos = 1;
        $trail = array_merge([['label' => __('messages.frontend), 'url' => url('/' . $this->locale)]], $this->items ?? []);

        foreach ($trail as $it) {
            if (! empty($it['label'])) {
                $ldItems[] = [
                    '@type' => 'ListItem',
                    'position' => $pos++,
                    'name' => $it['label'],
                    'item' => ! empty($it['url']) ? $it['url'] : url()->current(),
                ];
            }
        }

        return $ldItems;
    }
}
