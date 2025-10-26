<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Support\Monitoring\ApplicationMetrics;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

final class ObservabilityDashboard extends Page
{
    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations while surfacing
     * the accepted union type for static analyzers.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

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

    public function mount(ApplicationMetrics $metrics): void
    {
        $snapshot = $metrics->snapshot();
        $this->queueDepth = $snapshot['queues']['depth'];
        $this->queueMetrics = $snapshot['queues']['metrics'];
        $this->queueMetrics['failed_jobs_table'] = $snapshot['queues']['failed_jobs_table'];
        $this->cacheMetrics = $snapshot['cache'];
        $this->prometheusPreview = $metrics->toPrometheus();
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

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole(['super_admin', 'admin', 'administrator']);
        }

        return (bool) ($user->is_admin ?? false);
    }
}
