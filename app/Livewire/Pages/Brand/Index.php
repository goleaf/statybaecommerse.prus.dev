<?php

declare (strict_types=1);
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
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        // Initialize component
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('search')
                ->label(__('Search brands'))
                ->placeholder(__('Search brands...'))
                ->live(debounce: 300)
                ->afterStateUpdated(fn() => $this->resetPage())
                ->prefixIcon('heroicon-o-magnifying-glass'),
            Select::make('sortBy')
                ->label(__('Sort by'))
                ->options([
                    'name' => __('Name A-Z'),
                    'name_desc' => __('Name Z-A'),
                    'products_count' => __('Most Products'),
                    'created_at' => __('Newest'),
                    'featured' => __('Featured First')
                ])
                ->live()
                ->afterStateUpdated(fn() => $this->resetPage())
                ->prefixIcon('heroicon-o-arrows-up-down')
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
            'name' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'products_count' => $query->orderByDesc('products_count'),
            'created_at' => $query->orderByDesc('created_at'),
            'featured' => $query->orderByDesc('is_featured')->orderBy('name'),
            default => $query->orderBy('name'),
        };
        return $query->paginate(12);
    }
    /**
     * Handle getPageTitle functionality with proper error handling.
     * @return string
     */
    protected function getPageTitle(): string
    {
        return __('shared.brands');
    }
    /**
     * Handle getPageDescription functionality with proper error handling.
     * @return string|null
     */
    protected function getPageDescription(): ?string
    {
        return __('Browse all our trusted brand partners and discover quality products');
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.pages.brand.index')->title(__('translations.brands') . ' - ' . config('app.name'));
    }
}