<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Category;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\TagAwareCache;
use App\Support\FeatureState;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Navigation
 *
 * Livewire component for Navigation with reactive frontend functionality, real-time updates, and user interaction handling.
 */
class Navigation extends Component
{
    /**
     * Ensure locale is set on component boot.
     */
    public function boot(): void
    {
        // Ensure locale is set from route parameter on every request (including AJAX)
        // This must happen before any translations are used
        $this->ensureLocale();
    }

    /**
     * Ensure locale is set from route parameter.
     * This mirrors the SetLocale middleware logic to ensure locale is set correctly
     * for both initial page load and Livewire AJAX requests.
     */
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

    /**
     * Handle categories functionality with proper error handling.
     */
    #[Computed]
    public function categories(): Collection
    {
        $features = config('app-features.features', []);
        $categoryFeature = $features['category'] ?? null;
        $enableCategory = $categoryFeature instanceof FeatureState ? $categoryFeature === FeatureState::Enabled : (is_string($categoryFeature) ? strtolower($categoryFeature) === strtolower(FeatureState::Enabled->value) : (bool) $categoryFeature);
        if (! $enableCategory || ! class_exists(Category::class)) {
            return collect();
        }
        $locale = app()->getLocale();
        $cacheKey = "nav:categories:roots:{$locale}";

        return TagAwareCache::remember($cacheKey, now()->addMinutes(30), function () {
            $query = Category::query();
            if (method_exists(Category::class, 'isRoot')) {
                $query = $query->isRoot();
            } else {
                $query->whereNull('parent_id');
            }
            // Apply enabled scope or fallback column
            if (method_exists(Category::class, 'scopeEnabled')) {
                $query = $query->scopes(['enabled']);
            } else {
                $query->where('is_enabled', true);
            }

            return $query->orderBy('position')->get();
        }, [CacheKeys::homeTag()]);
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.components.navigation');
    }
}
