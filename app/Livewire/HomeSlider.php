<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Slider;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\TagAwareCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class HomeSlider extends Component
{
    public int $currentSlide = 0;

    public bool $autoPlay = true;

    public int $autoPlayInterval = 5000;

    public function boot(): void
    {
        // Ensure locale is set from route parameter on every request (including AJAX)
        // This must happen before any translations are used
        $this->ensureLocale();
    }

    public function mount(): void
    {
        // Ensure locale is set from route parameter
        $this->ensureLocale();
    }

    private function ensureLocale(): void
    {
        $request = request();

        // Get supported locales (config can be string or array)
        $supportedConfig = config('app.supported_locales', 'lt,en');
        $supportedLocales = [];
        if (is_array($supportedConfig)) {
            $supportedLocales = array_filter($supportedConfig, static fn ($locale): bool => is_string($locale) && $locale !== '');
        } elseif (is_string($supportedConfig)) {
            $supportedLocales = array_filter(
                array_map(
                    static fn (string $locale): string => trim($locale),
                    explode(',', $supportedConfig)
                ),
                static fn (string $locale): bool => $locale !== ''
            );
        }
        $supportedLocales = array_values(array_map(
            static fn (string $locale): string => trim($locale),
            $supportedLocales
        ));

        // Prefer locale from route parameter if present (e.g., /{locale}/...)
        $routeLocale = $request->route('locale');
        // Allow explicit override via query (?locale=xx)
        $queryLocale = $request->query('locale');

        // Get locale from query, header, session, cookie, or user preference
        $defaultLocaleConfig = config('app.locale', 'lt');
        $defaultLocale = is_string($defaultLocaleConfig) && $defaultLocaleConfig !== ''
            ? $defaultLocaleConfig
            : 'lt';

        $candidateLocales = array_values(array_filter([
            $routeLocale,
            $queryLocale,
            session('locale'),
            session('app.locale'),
            $request->cookie('app_locale'),
            auth()->check() ? (auth()->user()->preferred_locale ?? null) : null,
        ], static fn ($candidate): bool => is_string($candidate) && $candidate !== ''));

        $locale = $defaultLocale;

        foreach ($candidateLocales as $candidate) {
            if (in_array($candidate, $supportedLocales, true)) {
                $locale = $candidate;
                break;
            }
        }

        if (!in_array($locale, $supportedLocales, true)) {
            $fallbackLocaleConfig = config('app.fallback_locale');
            $fallbackLocale = is_string($fallbackLocaleConfig) && $fallbackLocaleConfig !== ''
                ? $fallbackLocaleConfig
                : $defaultLocale;

            if (in_array($fallbackLocale, $supportedLocales, true)) {
                $locale = $fallbackLocale;
            } elseif (in_array($defaultLocale, $supportedLocales, true)) {
                $locale = $defaultLocale;
            } elseif ($supportedLocales !== []) {
                $locale = $supportedLocales[0];
            } else {
                $locale = $defaultLocale;
            }
        }

        // Set application locale (this is critical for translations to work)
        app()->setLocale($locale);
        app()->instance('request_locale', $locale);

        // Store in session and cookie for persistence (mirror middleware behavior)
        session()->put('locale', $locale);
        session()->put('app.locale', $locale);
        cookie()->queue(cookie('app_locale', $locale, 60 * 24 * 30));
    }

    #[Computed]
    public function sliders(): Collection
    {
        $locale = app()->getLocale();

        return TagAwareCache::remember(
            CacheKeys::homeSliders($locale),
            CacheKeys::TTL_FIVE_MINUTES,
            function () use ($locale) {
                return Slider::query()
                    ->with(['translations' => fn ($q) => $q->where('locale', $locale)])
                    ->active()
                    ->ordered()
                    ->get();
            },
            [CacheKeys::homeTag()]
        );
    }

    public function nextSlide(): void
    {
        $maxSlides = $this->sliders->count() - 1;
        $this->currentSlide = $this->currentSlide >= $maxSlides ? 0 : $this->currentSlide + 1;
    }

    public function previousSlide(): void
    {
        $maxSlides = $this->sliders->count() - 1;
        $this->currentSlide = $this->currentSlide <= 0 ? $maxSlides : $this->currentSlide - 1;
    }

    public function goToSlide(int $index): void
    {
        $this->currentSlide = $index;
    }

    public function toggleAutoPlay(): void
    {
        $this->autoPlay = ! $this->autoPlay;
    }

    public function render(): View
    {
        return view('livewire.home-slider');
    }
}
