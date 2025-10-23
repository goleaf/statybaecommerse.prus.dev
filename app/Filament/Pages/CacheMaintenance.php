<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\Shared\ComponentPerformanceService;
use BackedEnum;
use UnitEnum;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Throwable;

final class CacheMaintenance extends Page
{
    /**
     * @var string|BackedEnum|null
     */
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-server-stack';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?string $title = 'Cache Maintenance';

    protected static ?string $slug = 'cache-maintenance';

    protected static ?string $navigationLabel = 'Cache Maintenance';

    protected static ?int $navigationSort = 95;

    protected string $view = 'filament.pages.cache-maintenance';

    public ?string $cacheKey = null;

    /**
     * @var array<int, string>
     */
    public array $cacheTags = [];

    /**
     * @var array<string, int|string|null>
     */
    public array $cachePerformanceSummary = [];

    /**
     * @var array<int, array{label: string, description?: string, url: string}>
     */
    public array $cachePolicyLinks = [];

    public function mount(): void
    {
        $this->refreshCacheMetrics();
        $this->cachePolicyLinks = $this->buildCachePolicyLinks();
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Targeted Cache Operations')
                    ->description('Use scoped operations before clearing broad cache areas to follow CachePolicy guidance.')
                    ->schema([
                        Forms\Components\TextInput::make('cacheKey')
                            ->label('Cache key')
                            ->helperText('Provide the exact cache key to invalidate. Leave empty to skip this operation.')
                            ->maxLength(255),
                        Forms\Components\TagsInput::make('cacheTags')
                            ->label('Cache tags')
                            ->helperText('Specify one or more cache tags to flush together. Tags are matched exactly.')
                            ->placeholder('discounts'),
                    ])
                    ->columns(1),
            ]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('forgetCacheKey')
                ->label('Forget Cache Key')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->action(function (): void {
                    $key = $this->cacheKey;
                    if ($key === null || $key === '') {
                        $this->notify('danger', 'Please provide a cache key to forget.');

                        return;
                    }

                    Cache::forget($key);
                    Notification::make()
                        ->title('Cache key cleared')
                        ->body("The cache entry '{$key}' was removed successfully.")
                        ->success()
                        ->send();
                    $this->refreshCacheMetrics();
                }),
            Action::make('flushCacheTags')
                ->label('Flush Cache Tags')
                ->icon('heroicon-o-tag')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (): void {
                    $tags = array_values(array_filter(array_map(
                        static fn (mixed $value): ?string => is_string($value) ? trim($value) : null,
                        $this->cacheTags
                    )));

                    if (empty($tags)) {
                        $this->notify('danger', 'Add at least one cache tag before flushing tagged cache entries.');

                        return;
                    }

                    try {
                        Cache::tags($tags)->flush();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Tagged cache unavailable')
                            ->body('The current cache driver does not support tag operations. ' . $exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Tagged cache flushed')
                        ->body('The following tags were cleared: ' . implode(', ', $tags))
                        ->success()
                        ->send();
                    $this->refreshCacheMetrics();
                }),
        ];
    }

    private function refreshCacheMetrics(): void
    {
        $service = app(ComponentPerformanceService::class);
        $summary = $service->getPerformanceReport();
        $this->cachePerformanceSummary = Arr::only($summary, [
            'total_components',
            'total_renders',
            'avg_render_time',
            'slowest_component',
            'slowest_time',
            'most_used_component',
            'most_used_count',
            'performance_score',
        ]);
    }

    /**
     * @return array<int, array{label: string, description?: string, url: string}>
     */
    private function buildCachePolicyLinks(): array
    {
        $links = [];

        if (Route::has('filament.admin.resources.documents.index')) {
            $links[] = [
                'label'       => 'CachePolicy Overview',
                'description' => 'Review the CachePolicy documentation before running destructive cache operations.',
                'url'         => route('filament.admin.resources.documents.index'),
            ];
        }

        if (Route::has('filament.admin.resources.documents.index')) {
            $links[] = [
                'label'       => 'CachePolicy Checklist',
                'description' => 'Search for "CachePolicy" within the Documents module for step-by-step guidance.',
                'url'         => route('filament.admin.resources.documents.index', [
                    'tableSearch' => 'CachePolicy',
                ]),
            ];
        }

        return $links;
    }
}
