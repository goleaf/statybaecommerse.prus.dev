<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Data\SearchQueryData;
use App\Models\Brand;
use App\Models\Category;
use App\Services\SearchService;
use BackedEnum;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use UnitEnum;

final class SearchExplorer extends Page
{
    protected static ?string $title = 'Search Explorer';

    protected static ?string $navigationLabel = 'Search Explorer';

    protected static UnitEnum|string|null $navigationGroup = 'Insights';

    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $slug = 'search-explorer';

    protected static string $view = 'filament.pages.search-explorer';

    public array $formData = [];

    /** @var array<string, array{items: array<int, array<string, mixed>>, total: int}> */
    public array $results = [];

    private SearchService $searchService;

    public function mount(): void
    {
        $this->searchService = app(SearchService::class);
        $this->formData = [
            'q' => '',
            'sort' => 'relevance',
            'per_page' => 10,
            'filters' => [
                'price_min' => null,
                'price_max' => null,
                'brand' => [],
                'category' => [],
            ],
        ];

        $this->form->fill($this->formData);
        $this->results = [
            'products' => ['items' => [], 'total' => 0],
            'categories' => ['items' => [], 'total' => 0],
            'brands' => ['items' => [], 'total' => 0],
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        TextInput::make('q')
                            ->label('Query')
                            ->required()
                            ->minLength(1)
                            ->maxLength(255),
                        Select::make('sort')
                            ->label('Sort by')
                            ->options([
                                'relevance' => 'Relevance',
                                'price' => 'Price',
                                'date' => 'Newest',
                            ])
                            ->default('relevance'),
                        TextInput::make('per_page')
                            ->label('Per type limit')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(SearchQueryData::MAX_PER_PAGE)
                            ->default(10),
                    ]),
                Grid::make(2)
                    ->schema([
                        TextInput::make('filters.price_min')
                            ->label('Price min')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                        TextInput::make('filters.price_max')
                            ->label('Price max')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                    ]),
                Grid::make(2)
                    ->schema([
                        Select::make('filters.brand')
                            ->label('Brands')
                            ->multiple()
                            ->options($this->brandOptions())
                            ->searchable(),
                        Select::make('filters.category')
                            ->label('Categories')
                            ->multiple()
                            ->options($this->categoryOptions())
                            ->searchable(),
                    ]),
            ])
            ->statePath('formData');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $queryString = trim((string) Arr::get($data, 'q', ''));
        if ($queryString === '') {
            $this->results = [
                'products' => ['items' => [], 'total' => 0],
                'categories' => ['items' => [], 'total' => 0],
                'brands' => ['items' => [], 'total' => 0],
            ];

            return;
        }

        $filters = Arr::get($data, 'filters', []);
        $query = new SearchQueryData(
            q: $queryString,
            price_min: $filters['price_min'] !== null && $filters['price_min'] !== '' ? (float) $filters['price_min'] : null,
            price_max: $filters['price_max'] !== null && $filters['price_max'] !== '' ? (float) $filters['price_max'] : null,
            brand_ids: $this->toIntArray(Arr::get($filters, 'brand', [])),
            category_ids: $this->toIntArray(Arr::get($filters, 'category', [])),
            sort: (string) Arr::get($data, 'sort', 'relevance'),
            page: 1,
            per_page: (int) min(max((int) Arr::get($data, 'per_page', 10), 1), SearchQueryData::MAX_PER_PAGE),
        );

        $results = $this->searchService->aggregate($query);

        $this->results = [
            'products' => [
                'items' => $results['products']['items']->values()->all(),
                'total' => $results['products']['total'],
            ],
            'categories' => [
                'items' => $results['categories']['items']->values()->all(),
                'total' => $results['categories']['total'],
            ],
            'brands' => [
                'items' => $results['brands']['items']->values()->all(),
                'total' => $results['brands']['total'],
            ],
        ];
    }

    private function brandOptions(): array
    {
        return Brand::query()
            ->orderBy('name')
            ->limit(100)
            ->pluck('name', 'id')
            ->toArray();
    }

    private function categoryOptions(): array
    {
        return Category::query()
            ->orderBy('name')
            ->limit(100)
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * @param array<int, mixed> $values
     * @return array<int, int>
     */
    private function toIntArray(array $values): array
    {
        return Collection::make($values)
            ->filter(static fn ($value): bool => is_numeric($value))
            ->map(static fn ($value): int => (int) $value)
            ->unique()
            ->values()
            ->all();
    }
}
