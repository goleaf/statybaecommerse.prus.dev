<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\Dashboard\DashboardTableRepository;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Number;

final class DashboardLowStockTable extends BaseTableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 1];

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
        return trans('admin/dashboard.tables.low_stock');
    }

    public function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
        return $table
            ->query(fn () => $this->tableRepository->lowStockProductsQuery()->limit(10))
            ->columns([
                TextColumn::make('sku')
                    ->label(trans('inventory.sku'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label(trans('inventory.product'))
                    ->wrap()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->name)
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label(trans('inventory.current_stock'))
                    ->formatStateUsing(fn ($state) => Number::format((int) $state, locale: app()->getLocale()))
                    ->color('danger')
                    ->sortable(),
                TextColumn::make('low_stock_threshold')
                    ->label(trans('inventory.low_stock_threshold'))
                    ->formatStateUsing(fn ($state) => Number::format((int) ($state ?? config('inventory.low_stock_threshold')), locale: app()->getLocale()))
                    ->sortable(),
                IconColumn::make('is_in_stock')
                    ->label(trans('inventory.in_stock'))
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\Action::make('manage')
                    ->label(trans('inventory.manage'))
                    ->icon('heroicon-m-adjustments-horizontal')
                    ->url(fn ($record) => route('filament.admin.resources.products.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->striped();
    }
}