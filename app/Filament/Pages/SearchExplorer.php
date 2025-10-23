<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Data\SearchQueryData;
use App\Services\SearchService;
use Filament\Pages\Page;
use UnitEnum;

final class SearchExplorer extends Page
{
    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-magnifying-glass-circle';

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

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole(['super_admin', 'admin', 'administrator']);
        }

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
