<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Collection;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
/**
 * CollectionProductsWidget
 * 
 * Filament v4 widget for CollectionProductsWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @property string|null $pollingInterval
 * @property int|string|array $columnSpan
 */
final class CollectionProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'admin.collections.widgets.products_heading';
    protected static ?int $sort = 3;
    protected ?string $pollingInterval = '30s';
    protected int|string|array $columnSpan = 'full';
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->query(Collection::query()->withCount('products')->where('is_visible', true)->orderBy('products_count', 'desc'))->columns([Tables\Columns\ImageColumn::make('image')->label(__('admin.collections.table.image'))->circular()->size(40), Tables\Columns\TextColumn::make('name')->label(__('admin.collections.table.name'))->searchable()->sortable()->weight('bold'), Tables\Columns\TextColumn::make('slug')->label(__('admin.collections.table.slug'))->searchable()->sortable()->copyable()->copyMessage(__('admin.copied')), Tables\Columns\TextColumn::make('products_count')->label(__('admin.collections.table.products_count'))->badge()->color('primary')->sortable(), Tables\Columns\IconColumn::make('is_automatic')->label(__('admin.collections.table.is_automatic'))->boolean()->trueIcon('heroicon-o-cog-6-tooth')->falseIcon('heroicon-o-hand-raised')->trueColor('info')->falseColor('gray'), Tables\Columns\TextColumn::make('display_type')->label(__('admin.collections.table.display_type'))->badge()->color(fn(string $state): string => match ($state) {
            'grid' => 'success',
            'list' => 'info',
            'carousel' => 'warning',
            default => 'gray',
        })->formatStateUsing(fn(string $state): string => __("admin.collections.display_types.{$state}")), Tables\Columns\TextColumn::make('created_at')->label(__('admin.collections.table.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->actions([Tables\Actions\Action::make('view')->label(__('admin.collections.actions.view'))->icon('heroicon-o-eye')->url(fn(Collection $record): string => route('filament.admin.resources.collections.view', $record))->openUrlInNewTab()])->defaultSort('products_count', 'desc')->paginated([10, 25, 50]);
    }
}