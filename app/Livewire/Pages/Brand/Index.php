<?php declare(strict_types=1);

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

final class Index extends AbstractPageComponent implements HasSchemas
{
    use InteractsWithSchemas;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'name')]
    public string $sortBy = 'name';

    public function mount(): void
    {
        // Initialize component
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('search')
                    ->label(__('Search brands'))
                    ->placeholder(__('Search brands...'))
                    ->live(debounce: 300)
                    ->afterStateUpdated(fn() => $this->resetPage()),

                Select::make('sortBy')
                    ->label(__('Sort by'))
                    ->options([
                        'name' => __('Name'),
                        'products_count' => __('Most Products'),
                        'created_at' => __('Newest'),
                    ])
                    ->live()
                    ->afterStateUpdated(fn() => $this->resetPage()),
            ]);
    }

    #[Computed]
    public function brands()
    {
        $query = Brand::query()
            ->with([
                'translations' => function ($q) {
                    $q->where('locale', app()->getLocale());
                },
                'media'
            ])
            ->where('is_enabled', true)
            ->withCount('products');

        // Apply search filter
        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('translations', function ($translationQuery) {
                      $translationQuery->where('locale', app()->getLocale())
                          ->where(function ($tq) {
                              $tq->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('description', 'like', '%' . $this->search . '%');
                          });
                  });
            });
        }

        // Apply sorting
        match ($this->sortBy) {
            'name' => $query->orderBy('name'),
            'products_count' => $query->orderByDesc('products_count'),
            'created_at' => $query->orderByDesc('created_at'),
            default => $query->orderBy('name'),
        };

        return $query->paginate(12);
    }

    protected function getPageTitle(): string
    {
        return __('shared.brands');
    }

    protected function getPageDescription(): ?string
    {
        return __('Browse all our trusted brand partners and discover quality products');
    }

    public function render(): View
    {
        return view('livewire.pages.brand.index')
            ->title(__('translations.brands') . ' - ' . config('app.name'));
    }
}