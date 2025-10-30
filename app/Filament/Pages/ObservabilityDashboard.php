<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Support\Monitoring\ApplicationMetrics;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

final class ObservabilityDashboard extends Page
{
    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations while surfacing
     * the accepted union type for static analyzers.
     */
//    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-chart-bar';
    }

    protected static UnitEnum|string|null $navigationGroup = 'System';

    protected static ?string $title = 'Observability';

    protected static ?string $slug = 'observability';

    protected static ?int $navigationSort = 96;

    protected string $view = 'filament.pages.observability-dashboard';

    /**
     * @var array<int, array{connection: string, queue: string, size: int}>
     */
    public array $queueDepth = [];

    /**
     * @var array<string, mixed>
     */
    public array $queueMetrics = [];

    /**
     * @var array<string, mixed>
     */
    public array $cacheMetrics = [];

    public string $prometheusPreview = '';

    /**
     * @var array<int, array{number: int, title: string, description: string, status: string, url: string|null}>
     */
    public array $openPrWatchList = [];

    public function mount(ApplicationMetrics $metrics): void
    {
        $snapshot = $metrics->snapshot();
        $this->queueDepth = $snapshot['queues']['depth'];
        $this->queueMetrics = $snapshot['queues']['metrics'];
        $this->queueMetrics['failed_jobs_table'] = $snapshot['queues']['failed_jobs_table'];
        $this->cacheMetrics = $snapshot['cache'];
        $this->prometheusPreview = $metrics->toPrometheus();
        // Populate the open pull request watch list so engineering can quickly
        // assess merge readiness for the highlighted workstreams.
        $this->openPrWatchList = $this->buildOpenPrWatchList();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if ($user === null) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole')) { // @phpstan-ignore-line Intentionally guard dynamic guard methods surfaced via traits.
            return $user->hasAnyRole(['super_admin', 'admin', 'administrator']);
        }

        return (bool) ($user->is_admin ?? false);
    }

    /**
     * Build the curated watch list describing open pull requests that require
     * extra visibility while reviews are underway.
     *
     * @return array<int, array{number: int, title: string, description: string, status: string, url: string|null}>
     */
    private function buildOpenPrWatchList(): array
    {
        // Encode the pull request metadata explicitly so future updates only
        // need to tweak the array without reverse-engineering any service calls.
        return [
            [
                'number'      => 1566,
                'title'       => 'Campaign analytics calc refresh',
                'description' => 'Rename chart series/legends and source metrics from normalised periods.',
                'status'      => 'watching',
                'url'         => 'https://github.com/prus-dev/statybaecommerse.prus.dev/pull/1566',
            ],
            [
                'number'      => 1565,
                'title'       => 'Recommendation caching / manual strategy',
                'description' => 'Allow curated blocks in UI and avoid assuming purely algorithmic feeds.',
                'status'      => 'watching',
                'url'         => 'https://github.com/prus-dev/statybaecommerse.prus.dev/pull/1565',
            ],
            [
                'number'      => 1564,
                'title'       => 'Partner inventory pager',
                'description' => 'Ensure query retention in pager interactions.',
                'status'      => 'watching',
                'url'         => 'https://github.com/prus-dev/statybaecommerse.prus.dev/pull/1564',
            ],
            [
                'number'      => 1554,
                'title'       => 'Order controller refactor',
                'description' => 'Verify shared lookups maintain status filter behaviour.',
                'status'      => 'watching',
                'url'         => 'https://github.com/prus-dev/statybaecommerse.prus.dev/pull/1554',
            ],
            [
                'number'      => 1540,
                'title'       => 'Partner order summary metrics',
                'description' => 'Add small metric cards for count and revenue above the order table.',
                'status'      => 'watching',
                'url'         => 'https://github.com/prus-dev/statybaecommerse.prus.dev/pull/1540',
            ],
            [
                'number'      => 1537,
                'title'       => 'News filtering',
                'description' => 'Introduce filter chips and empty-state messaging for newsroom flows.',
                'status'      => 'watching',
                'url'         => 'https://github.com/prus-dev/statybaecommerse.prus.dev/pull/1537',
            ],
            [
                'number'      => 1518,
                'title'       => 'Content-based recommendations telemetry',
                'description' => 'Capture behaviour logging events from the UI, covering click and seen actions.',
                'status'      => 'watching',
                'url'         => 'https://github.com/prus-dev/statybaecommerse.prus.dev/pull/1518',
            ],
            [
                'number'      => 1511,
                'title'       => 'Legal translations parity',
                'description' => 'Confirm admin forms and storefront operate with multilingual legal/product/brand fields.',
                'status'      => 'watching',
                'url'         => 'https://github.com/prus-dev/statybaecommerse.prus.dev/pull/1511',
            ],
            [
                'number'      => 1508,
                'title'       => 'Product translations parity',
                'description' => 'Ensure product translations align across admin and storefront surfaces.',
                'status'      => 'watching',
                'url'         => 'https://github.com/prus-dev/statybaecommerse.prus.dev/pull/1508',
            ],
            [
                'number'      => 1488,
                'title'       => 'Brand translations parity',
                'description' => 'Validate localized brand fields read and write consistently.',
                'status'      => 'watching',
                'url'         => 'https://github.com/prus-dev/statybaecommerse.prus.dev/pull/1488',
            ],
        ];
    }
}
