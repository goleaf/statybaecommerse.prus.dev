<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Zone;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
/**
 * RecentZonesWidget
 * 
 * Filament v4 widget for RecentZonesWidget dashboard display with real-time data and interactive features.
 * 
 * @property int|string|array $columnSpan
 * @property int|null $sort
 * @property string|null $heading
 */
final class RecentZonesWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 3;
    protected static ?string $heading = 'zones.recent_zones';
    /**
     * Handle getDescription functionality with proper error handling.
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return 'zones.recent_zones_desc';
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->query(Zone::query()->with(['currency', 'countries'])->withCount('countries')->latest()->limit(10))->columns([Tables\Columns\TextColumn::make('name')->label(__('zones.name'))->searchable()->sortable()->weight('bold'), Tables\Columns\TextColumn::make('code')->label(__('zones.code'))->searchable()->sortable()->badge()->color('gray'), Tables\Columns\TextColumn::make('type')->label(__('zones.type'))->badge()->color(fn(string $state): string => match ($state) {
            'shipping' => 'info',
            'tax' => 'warning',
            'payment' => 'success',
            'delivery' => 'primary',
            'general' => 'gray',
            default => 'gray',
        })->formatStateUsing(fn(string $state): string => match ($state) {
            'shipping' => __('zones.type_shipping'),
            'tax' => __('zones.type_tax'),
            'payment' => __('zones.type_payment'),
            'delivery' => __('zones.type_delivery'),
            'general' => __('zones.type_general'),
            default => $state,
        }), Tables\Columns\TextColumn::make('currency.name')->label(__('zones.currency'))->sortable(), Tables\Columns\TextColumn::make('countries_count')->label(__('zones.countries_count'))->counts('countries')->sortable()->alignCenter(), Tables\Columns\TextColumn::make('tax_rate')->label(__('zones.tax_rate'))->formatStateUsing(fn(string $state): string => $state . '%')->sortable()->alignEnd(), Tables\Columns\TextColumn::make('shipping_rate')->label(__('zones.shipping_rate'))->formatStateUsing(fn(string $state): string => '€' . $state)->sortable()->alignEnd(), Tables\Columns\IconColumn::make('is_enabled')->label(__('zones.is_enabled'))->boolean()->alignCenter(), Tables\Columns\IconColumn::make('is_active')->label(__('zones.is_active'))->boolean()->alignCenter(), Tables\Columns\IconColumn::make('is_default')->label(__('zones.is_default'))->boolean()->alignCenter(), Tables\Columns\TextColumn::make('created_at')->label(__('zones.created_at'))->dateTime()->sortable()->since()])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])->poll('60s');
    }
}