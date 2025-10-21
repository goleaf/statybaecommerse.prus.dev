<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\Dashboard\DashboardTableRepository;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Illuminate\Support\Facades\Gate;

final class DashboardRecentErrorsTable extends BaseTableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 2];

    public function __construct(private readonly DashboardTableRepository $tableRepository)
    {
        parent::__construct();
    }

    public static function canView(): bool
    {
        return Gate::allows(config('dashboard.permissions.view_tables'));
    }

    public function getHeading(): ?string
    {
        return trans('admin/dashboard.tables.recent_errors');
    }

    public function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->query(fn () => $this->tableRepository->recentFailedJobsQuery()->limit(10))
            ->columns([
                TextColumn::make('job_name')
                    ->label(trans('admin/dashboard.errors.job'))
                    ->wrap()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->job_name),
                TextColumn::make('queue')
                    ->label(trans('admin/dashboard.errors.queue'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('connection')
                    ->label(trans('admin/dashboard.errors.connection'))
                    ->badge(),
                TextColumn::make('failed_at')
                    ->label(trans('admin/dashboard.errors.failed_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('exception')
                    ->label(trans('admin/dashboard.errors.exception'))
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->exception),
            ])
            ->actions([
                Tables\Actions\Action::make('retry')
                    ->label(trans('admin/dashboard.errors.retry'))
                    ->icon('heroicon-m-arrow-path')
                    ->requiresConfirmation()
                    ->disabled()
                    ->tooltip(trans('admin/dashboard.errors.retry_placeholder')),
            ])
            ->emptyStateHeading(trans('admin/dashboard.errors.no_failures'))
            ->emptyStateDescription(trans('admin/dashboard.errors.no_failures_description'))
            ->striped();
    }
}
