<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Data\SearchQueryData;
use App\Services\SearchService;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

final class SearchExplorer extends Page
{
    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations while keeping
     * the union types expressed through PHPDoc for clarity and tooling support.
     */
//    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-magnifying-glass-circle';
    }

    public static function getNavigationGroup(): BackedEnum|string|null
    {
        return 'Search'; // Keep discovery tooling under the dedicated search navigation bucket.
    }

    protected static ?string $title = 'Search Explorer';

    protected static ?string $slug = 'search-explorer';

    protected string $view = 'filament.pages.search-explorer';

    public string $query = '';

    public int $perPage = SearchQueryData::DEFAULT_PER_PAGE;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $results = [];

    /**
     * @var array<string, mixed>
     */
    public array $meta = [];

    /**
     * @var array<string, int>
     */
    public array $buckets = [
        'product'  => 0,
        'category' => 0,
        'brand'    => 0,
    ];

    public function mount(): void
    {
        $this->meta = [
            'query'         => '',
            'page'          => 1,
            'per_page'      => $this->perPage,
            'total_results' => 0,
            'returned'      => 0,
            'cached'        => false,
            'types'         => ['product', 'category', 'brand'],
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super_admin', 'admin', 'administrator'])) {
            return true;
        }

        // Honour legacy admin toggles so environments without role seeding can
        // still surface the search diagnostics tooling.
        return (bool) ($user->is_admin ?? false);
    }

    public function performSearch(): void
    {
        $query = trim($this->query);
        $perPage = max(1, min(SearchQueryData::MAX_PER_PAGE, $this->perPage));

        $this->perPage = $perPage;

        if ($query === '') {
            $this->results = [];
            $this->meta = [
                'query'         => '',
                'page'          => 1,
                'per_page'      => $perPage,
                'total_results' => 0,
                'returned'      => 0,
                'cached'        => false,
                'types'         => ['product', 'category', 'brand'],
            ];
            $this->buckets = [
                'product'  => 0,
                'category' => 0,
                'brand'    => 0,
            ];

            return;
        }

        $queryData = SearchQueryData::fromArray([
            'query'    => $query,
            'page'     => 1,
            'per_page' => $perPage,
        ], [
            'source'  => 'filament.search-explorer',
            'user_id' => auth()->id(),
            'locale'  => app()->getLocale(),
        ]);

        /** @var SearchService $service */
        $service = app(SearchService::class);
        $response = $service->search($queryData);

        $this->results = $response['data'] ?? [];
        $this->meta = $response['meta'] ?? $this->meta;
        $this->buckets = $response['buckets'] ?? $this->buckets;
    }
}
