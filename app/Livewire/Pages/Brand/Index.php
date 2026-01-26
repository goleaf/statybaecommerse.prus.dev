<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Brand;

use App\Livewire\Pages\AbstractPageComponent;
use App\Models\Brand;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

/**
 * Index
 *
 * Livewire component for Index with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $search
 * @property string $sortBy
 */
final class Index extends AbstractPageComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'name')]
    public string $sortBy = 'name';

    public bool $sidebarOpen = false;

    /**
     * Boot the component and ensure locale is set.
     */
    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        // Mount method can be empty or contain other initialization logic
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('search')
                ->label(__('messages.brands_index_search_label'))
                ->placeholder(__('messages.brands_index_search_placeholder'))
                ->live(debounce: 300)
                ->afterStateUpdated(fn () => $this->resetPage())
                ->prefixIcon('heroicon-o-magnifying-glass'),
            Select::make('sortBy')
                ->label(__('messages.brands_index_sort_label'))
                ->options([
                    'name'           => __('messages.brands_index_sort_option_name'),
                    'name_desc'      => __('messages.brands_index_sort_option_name_desc'),
                    'products_count' => __('messages.brands_index_sort_option_products'),
                    'created_at'     => __('messages.brands_index_sort_option_newest'),
                    'featured'       => __('messages.brands_index_sort_option_featured'),
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

    #[Computed]
    public function totalBrands(): int
    {
        return $this->brands->total();
    }

    #[Computed]
    public function activeFilterCount(): int
    {
        return collect([
            filled($this->search),
            $this->sortBy !== 'name',
        ])->filter()->count();
    }

    /**
     * Handle getPageTitle functionality with proper error handling.
     */
    protected function getPageTitle(): string
    {
        return __('messages.brands_index_meta_title');
    }

    /**
     * Handle getPageDescription functionality with proper error handling.
     */
    protected function getPageDescription(): ?string
    {
        return __('messages.brands_index_meta_description');
    }

    /**
     * Reset filters to their default state.
     */
    public function clearFilters(): void
    {
        $this->reset(['search', 'sortBy']);
        $this->resetPage();
    }

    public function paginationUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $queryParameters = request()->except('page');
        if ($queryParameters === []) {
            return $url;
        }

        $queryString = http_build_query($queryParameters);

        return $url . (str_contains($url, '?') ? '&' : '?') . $queryString;
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.pages.brand.index', [
            'paginator' => $this->brands,
            'totalBrands' => $this->totalBrands(),
            'activeFilterCount' => $this->activeFilterCount(),
        ])->title(__('messages.brands_index_meta_title') . ' - ' . config('app.name'));
    }
}
