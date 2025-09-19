<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\VariantStockHistoryResource\Pages;
use App\Models\VariantStockHistory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use UnitEnum;

final class VariantStockHistoryResource extends Resource
{
    protected static ?string $model = VariantStockHistory::class;
    // protected static $navigationIcon = 'heroicon-o-archive-box';
    // protected static $navigationGroup = NavigationGroup::System;
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('admin.variant_stock_histories.navigation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.variant_stock_histories.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.variant_stock_histories.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $form
            ->components([
                Section::make(__('admin.variant_stock_histories.sections.basic_info'))
                    ->description(__('admin.variant_stock_histories.sections.basic_info_description'))
                    ->schema([
                        Select::make('variant_id')
                            ->label(__('admin.variant_stock_histories.fields.variant'))
                            ->relationship('variant', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('old_quantity')
                                    ->label(__('admin.variant_stock_histories.fields.old_quantity'))
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('new_quantity')
                                    ->label(__('admin.variant_stock_histories.fields.new_quantity'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),
                            ]),
                        TextInput::make('quantity_change')
                            ->label(__('admin.variant_stock_histories.fields.quantity_change'))
                            ->disabled(),
                        Grid::make(2)
                            ->schema([
                                Select::make('change_type')
                                    ->label(__('admin.variant_stock_histories.fields.change_type'))
                                    ->options([
                                        'increase' => __('admin.variant_stock_histories.change_types.increase'),
                                        'decrease' => __('admin.variant_stock_histories.change_types.decrease'),
                                        'adjustment' => __('admin.variant_stock_histories.change_types.adjustment'),
                                        'reserve' => __('admin.variant_stock_histories.change_types.reserve'),
                                        'unreserve' => __('admin.variant_stock_histories.change_types.unreserve'),
                                    ])
                                    ->required(),
                                Select::make('change_reason')
                                    ->label(__('admin.variant_stock_histories.fields.change_reason'))
                                    ->options([
                                        'sale' => __('admin.variant_stock_histories.change_reasons.sale'),
                                        'return' => __('admin.variant_stock_histories.change_reasons.return'),
                                        'adjustment' => __('admin.variant_stock_histories.change_reasons.adjustment'),
                                        'reserve' => __('admin.variant_stock_histories.change_reasons.reserve'),
                                        'unreserve' => __('admin.variant_stock_histories.change_reasons.unreserve'),
                                        'damage' => __('admin.variant_stock_histories.change_reasons.damage'),
                                        'theft' => __('admin.variant_stock_histories.change_reasons.theft'),
                                        'expired' => __('admin.variant_stock_histories.change_reasons.expired'),
                                        'manual' => __('admin.variant_stock_histories.change_reasons.manual'),
                                    ])
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('changed_by')
                                    ->label(__('admin.variant_stock_histories.fields.changed_by'))
                                    ->relationship('changedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('reference_type')
                                    ->label(__('admin.variant_stock_histories.fields.reference_type'))
                                    ->options([
                                        'order' => 'Order',
                                        'reservation' => 'Reservation',
                                    ]),
                            ]),
                        TextInput::make('reference_id')
                            ->label(__('admin.variant_stock_histories.fields.reference_id'))
                            ->numeric(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variant.name')
                    ->label(__('admin.variant_stock_histories.fields.variant'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('old_quantity')
                    ->label(__('admin.variant_stock_histories.fields.old_quantity'))
                    ->sortable(),
                TextColumn::make('new_quantity')
                    ->label(__('admin.variant_stock_histories.fields.new_quantity'))
                    ->sortable(),
                TextColumn::make('quantity_change')
                    ->label(__('admin.variant_stock_histories.fields.quantity_change'))
                    ->getStateUsing(function ($record) {
                        $change = $record->new_quantity - $record->old_quantity;
                        $sign = $change >= 0 ? '+' : '';
                        return $sign . $change;
                    })
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger'),
                BadgeColumn::make('change_type')
                    ->label(__('admin.variant_stock_histories.fields.change_type'))
                    ->colors([
                        'success' => 'increase',
                        'danger' => 'decrease',
                        'warning' => 'adjustment',
                        'info' => 'reserve',
                        'secondary' => 'unreserve',
                    ]),
                BadgeColumn::make('change_reason')
                    ->label(__('admin.variant_stock_histories.fields.change_reason'))
                    ->colors([
                        'success' => 'sale',
                        'info' => 'return',
                        'primary' => 'reserve',
                        'danger' => 'damage',
                        'danger' => 'theft',
                        'warning' => 'expired',
                        'gray' => 'manual',
                    ]),
                TextColumn::make('changedBy.name')
                    ->label(__('admin.variant_stock_histories.fields.changed_by'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reference_type')
                    ->label(__('admin.variant_stock_histories.fields.reference_type'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reference_id')
                    ->label(__('admin.variant_stock_histories.fields.reference_id'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin.variant_stock_histories.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('change_type')
                    ->label(__('admin.variant_stock_histories.filters.change_type'))
                    ->options([
                        'increase' => __('admin.variant_stock_histories.change_types.increase'),
                        'decrease' => __('admin.variant_stock_histories.change_types.decrease'),
                        'adjustment' => __('admin.variant_stock_histories.change_types.adjustment'),
                        'reserve' => __('admin.variant_stock_histories.change_types.reserve'),
                        'unreserve' => __('admin.variant_stock_histories.change_types.unreserve'),
                    ]),
                SelectFilter::make('change_reason')
                    ->label(__('admin.variant_stock_histories.filters.change_reason'))
                    ->options([
                        'sale' => __('admin.variant_stock_histories.change_reasons.sale'),
                        'return' => __('admin.variant_stock_histories.change_reasons.return'),
                        'adjustment' => __('admin.variant_stock_histories.change_reasons.adjustment'),
                        'reserve' => __('admin.variant_stock_histories.change_reasons.reserve'),
                        'unreserve' => __('admin.variant_stock_histories.change_reasons.unreserve'),
                        'damage' => __('admin.variant_stock_histories.change_reasons.damage'),
                        'theft' => __('admin.variant_stock_histories.change_reasons.theft'),
                        'expired' => __('admin.variant_stock_histories.change_reasons.expired'),
                        'manual' => __('admin.variant_stock_histories.change_reasons.manual'),
                    ]),
                SelectFilter::make('variant_id')
                    ->label(__('admin.variant_stock_histories.filters.variant'))
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->label(__('admin.variant_stock_histories.filters.created_at'))
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('admin.variant_stock_histories.filters.created_from')),
                        DatePicker::make('created_until')
                            ->label(__('admin.variant_stock_histories.filters.created_until')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn($query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVariantStockHistories::route('/'),
            'create' => Pages\CreateVariantStockHistory::route('/create'),
            'view' => Pages\ViewVariantStockHistory::route('/{record}'),
            'edit' => Pages\EditVariantStockHistory::route('/{record}/edit'),
        ];
    }
}
