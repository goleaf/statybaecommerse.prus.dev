<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\Dashboard\DashboardTableRepository;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Number;

final class DashboardRecentOrdersTable extends BaseTableWidget
{
    protected static ?int $sort = 3;

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
        return trans('admin/dashboard.tables.recent_orders');
    }

    public function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
        return $table
            ->query(fn () => $this->tableRepository->recentOrdersQuery()->limit(10))
            ->columns([
                TextColumn::make('number')
                    ->label(trans('orders.number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(trans('orders.status'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state) => $state ? trans("orders.statuses.$state") : trans('admin/dashboard.tables.status_unknown')),
                TextColumn::make('user.name')
                    ->label(trans('orders.customer'))
                    ->description(fn ($record) => $record?->user?->email ?? trans('admin/dashboard.tables.guest_customer'))
                    ->url(fn ($record) => $record?->user ? route('filament.admin.resources.users.view', $record->user) : null)
                    ->openUrlInNewTab()
                    ->limit(20)
                    ->sortable(),
                TextColumn::make('total')
                    ->label(trans('orders.total_amount'))
                    ->formatStateUsing(fn ($state) => Number::currency((float) $state, 'EUR', locale: app()->getLocale()))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('orders.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(trans('orders.view'))
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => route('filament.admin.resources.orders.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->striped();
    }
}