<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Brand;

use App\Livewire\Pages\AbstractPageComponent;
use App\Models\Brand;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Index
 *
 * Livewire component for Index with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $search
 * @property string $sortBy
 */
final class Index extends AbstractPageComponent implements HasSchemas
{
    use InteractsWithSchemas;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'name')]
    public string $sortBy = 'name';

    public bool $sidebarOpen = false;

    /**
     * Boot the component and ensure locale is set.
     */
    public function boot(): void
    {
        // Ensure locale is set from route parameter on every request (including AJAX)
        // This must happen before any translations are used
        $this->ensureLocale();
    }

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        // Ensure locale is set from route parameter
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
     * Configure the Filament form schema with fields and validation.
     */
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('search')
                ->label(__('brands_index_search_label'))
                ->placeholder(__('brands_index_search_placeholder'))
                ->live(debounce: 300)
                ->afterStateUpdated(fn () => $this->resetPage())
                ->prefixIcon('heroicon-o-magnifying-glass'),
            Select::make('sortBy')
                ->label(__('brands_index_sort_label'))
                ->options([
                    'name'           => __('brands_index_sort_option_name'),
                    'name_desc'      => __('brands_index_sort_option_name_desc'),
                    'products_count' => __('brands_index_sort_option_products'),
                    'created_at'     => __('brands_index_sort_option_newest'),
                    'featured'       => __('brands_index_sort_option_featured'),
                ])
                ->live()
                ->afterStateUpdated(fn () => $this->resetPage())
                ->prefixIcon('heroicon-o-arrows-up-down'),
        ]);
    }

    /**
     * Handle brands functionality with proper error handling.
     */
    #[Computed]
    public function brands()
    {
        $query = Brand::query()->with(['translations' => function ($q) {
            $q->where('locale', app()->getLocale());
        }, 'media'])->where('is_enabled', true)->withCount('products');
        // Apply search filter
        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')->orWhere('description', 'like', '%' . $this->search . '%')->orWhereHas('translations', function ($translationQuery) {
                    $translationQuery->where('locale', app()->getLocale())->where(function ($tq) {
                        $tq->where('name', 'like', '%' . $this->search . '%')->orWhere('description', 'like', '%' . $this->search . '%');
                    });
                });
            });
        }
        // Apply sorting
        match ($this->sortBy) {
            'name'           => $query->orderBy('name'),
            'name_desc'      => $query->orderByDesc('name'),
            'products_count' => $query->orderByDesc('products_count'),
            'created_at'     => $query->orderByDesc('created_at'),
            'featured'       => $query->orderByDesc('is_featured')->orderBy('name'),
            default          => $query->orderBy('name'),
        };

        return $query->paginate(12);
    }

    /**
     * Handle getPageTitle functionality with proper error handling.
     */
    protected function getPageTitle(): string
    {
        return __('brands_index_meta_title');
    }

    /**
     * Handle getPageDescription functionality with proper error handling.
     */
    protected function getPageDescription(): ?string
    {
        return __('brands_index_meta_description');
    }

    /**
     * Reset filters to their default state.
     */
    public function clearFilters(): void
    {
        $this->reset(['search', 'sortBy']);
        $this->resetPage();
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        // Ensure locale is set before rendering (boot should handle it, but double-check)
        $this->ensureLocale();
        
        return view('livewire.pages.brand.index')->title(__('brands_index_meta_title') . ' - ' . config('app.name'));
    }
}
