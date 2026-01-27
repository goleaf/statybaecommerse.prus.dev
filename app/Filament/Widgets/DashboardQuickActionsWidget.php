<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Jobs\ClearApplicationCacheJob;
use App\Jobs\RebuildSearchIndexJob;
use App\Jobs\RunMinimalSeedJob;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

final class DashboardQuickActionsWidget extends Widget implements HasActions
{
    use InteractsWithActions;

    protected static ?int $sort = 6;

    protected string $view = 'filament.widgets.dashboard.quick-actions';

    protected int|string|array $columnSpan = 'full';

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        return null;
    }

    public static function canView(): bool
    {
        return Gate::allows(config('dashboard.permissions.run_actions'));
    }

    public function rebuildSearchIndexAction(): Action
    {
        return Action::make('rebuildSearchIndex')
            ->label(trans('admin/dashboard.actions.rebuild_search'))
            ->icon('heroicon-m-magnifying-glass-circle')
            ->color('primary')
            ->authorize(fn () => Gate::allows(config('dashboard.permissions.run_actions')))
            ->requiresConfirmation()
            ->modalHeading(trans('admin/dashboard.actions.rebuild_search_heading'))
            ->modalDescription(trans('admin/dashboard.actions.rebuild_search_confirm'))
            ->action(function (): void {
                RebuildSearchIndexJob::dispatch();
                Log::info('Dashboard quick action triggered: rebuild search index.');
            });
    }

    public function clearCacheAction(): Action
    {
        return Action::make('clearCache')
            ->label(trans('admin/dashboard.actions.clear_cache'))
            ->icon('heroicon-m-trash')
            ->color('warning')
            ->authorize(fn () => Gate::allows(config('dashboard.permissions.run_actions')))
            ->requiresConfirmation()
            ->modalHeading(trans('admin/dashboard.actions.clear_cache_heading'))
            ->modalDescription(trans('admin/dashboard.actions.clear_cache_confirm'))
            ->action(function (): void {
                ClearApplicationCacheJob::dispatch();
                Log::info('Dashboard quick action triggered: clear cache.');
            });
    }

    public function runMinimalSeedAction(): Action
    {
        return Action::make('runMinimalSeed')
            ->label(trans('admin/dashboard.actions.run_minimal_seed'))
            ->icon('heroicon-m-bolt')
            ->color('success')
            ->authorize(fn () => Gate::allows(config('dashboard.permissions.run_actions')))
            ->requiresConfirmation()
            ->modalHeading(trans('admin/dashboard.actions.run_minimal_seed_heading'))
            ->modalDescription(trans('admin/dashboard.actions.run_minimal_seed_confirm'))
            ->action(function (): void {
                RunMinimalSeedJob::dispatch();
                Log::info('Dashboard quick action triggered: run minimal seed.');
            });
    }
}
